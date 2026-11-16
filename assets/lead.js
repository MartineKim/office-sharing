/* BizStory direct Google Apps Script delivery.
   sendBeacon queues a small text/plain request promptly and preserves the original payload.
   A queued request is NOT proof of spreadsheet storage. */
(function(){
  const SCRIPT_URL = "https://script.google.com/macros/s/AKfycbw5P-hbWNdaQKDHlD_EJBExLu1Dyoj6CtFOy5Msfzcpb0Dc5rKrxHISRA0g3pY2OZoCjg/exec";
  // Attribution survives navigation between homepage and industry lookup, for this tab only.
  const TRACK_KEY="bizstory_attribution_v1";
  const PARAMS=["utm_source","utm_medium","utm_campaign","utm_content","utm_term"];
  function getAttribution(){
    const query=new URLSearchParams(location.search);
    let previous={};
    try{
      const raw=JSON.parse(sessionStorage.getItem(TRACK_KEY)||"{}");
      if(raw && typeof raw==="object" && Date.now()-Number(raw.time||0)<30*60*1000) previous=raw;
    }catch(_){}
    const explicit=PARAMS.some(k=>query.has(k));
    let data=explicit?{time:Date.now()}:previous;
    for(const key of PARAMS){
      const value=query.get(key);
      if(value) data[key]=value.slice(0,160);
    }
    // No UTM: document.referrer may disclose only the originating domain.
    if(!data.utm_source && document.referrer){
      try{
        const host=new URL(document.referrer).hostname.toLowerCase();
        if(host!==location.hostname && !host.endsWith("."+location.hostname)){
          const domains=[
            [/\\.naver\\.com$|^naver\\.com$/,"naver"],
            [/\\.google\\.com$|^google\\.com$|\\.google\\.co\\.kr$/,"google"],
            [/\\.instagram\\.com$|^instagram\\.com$/,"instagram"],
            [/\\.facebook\\.com$|^facebook\\.com$/,"facebook"],
            [/\\.youtube\\.com$|^youtube\\.com$/,"youtube"],
            [/\\.threads\\.net$|^threads\\.net$/,"threads"],
            [/\\.daangn\\.com$|^daangn\\.com$/,"daangn"],
            [/\\.kakao\\.com$|^kakao\\.com$/,"kakao"]
          ];
          const found=domains.find(([pattern])=>pattern.test(host));
          if(found)data.utm_source=found[1];
        }
      }catch(_){}
    }
    if(data.utm_source){
      data.time=Date.now();
      try{sessionStorage.setItem(TRACK_KEY,JSON.stringify(data))}catch(_){}
    }
    return data;
  }
  window.bizstoryGetAttribution=getAttribution;
  getAttribution();
  function bodyFor(payload){
    if(!payload || typeof payload!=="object")throw new Error("잘못된 전송 데이터");
    if(payload.type==="industrySearch"){
      return {
        type:"industrySearch",keyword:payload.keyword,matchedName:payload.matchedName,
        matchedCode:payload.matchedCode,status:payload.status,
        channel:payload.channel,campaign:payload.campaign
      };
    }
    if(payload.website)throw new Error("자동입력 방지");
    const {name,phone,inquiryType,message,channel,campaign,adContent,keyword}=payload;
    if(!name||!phone||payload.consent!==true)throw new Error("필수 입력 및 개인정보 동의를 확인해 주세요.");
    return {name,phone,inquiryType,message,channel,campaign,adContent,keyword};
  }
  async function send(payload){
    const text=JSON.stringify(bodyFor(payload));
    const data=new Blob([text],{type:"text/plain;charset=utf-8"});
    if(navigator.sendBeacon && navigator.sendBeacon(SCRIPT_URL,data)){
      return {ok:true,confirmed:false,queued:true};
    }
    const controller=new AbortController();
    const timer=setTimeout(()=>controller.abort(),6000);
    try{
      await fetch(SCRIPT_URL,{
        method:"POST",mode:"no-cors",keepalive:true,
        headers:{"Content-Type":"text/plain;charset=utf-8"},
        body:text,signal:controller.signal
      });
      return {ok:true,confirmed:false,queued:true};
    }catch(error){
      throw new Error("전송 상태 확인 불가: "+(error?.name||"network"));
    }finally{clearTimeout(timer)}
  }
  window.bizstorySubmitLead=send;
  window.bizstoryTrackSearch=payload=>send(payload).catch(e=>console.debug("검색 로그 전송 지연",e.message));
})();