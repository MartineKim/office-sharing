/* BizStory restoration: original Google Apps Script direct POST, without an unverified Pages API dependency. */
(function(){
  const SCRIPT_URL = "https://script.google.com/macros/s/AKfycbw5P-hbWNdaQKDHlD_EJBExLu1Dyoj6CtFOy5Msfzcpb0Dc5rKrxHISRA0g3pY2OZoCjg/exec";
  async function send(payload){
    if(!payload || typeof payload !== "object") throw new Error("잘못된 전송 데이터");
    // Apps Script relies on original fields; do not send new frontend-only keys.
    let body;
    if(payload.type === "industrySearch"){
      body = {
        type:"industrySearch",keyword:payload.keyword,matchedName:payload.matchedName,
        matchedCode:payload.matchedCode,status:payload.status,
        channel:payload.channel,campaign:payload.campaign
      };
    }else{
      if(payload.website)throw new Error("자동입력 방지");
      const {name,phone,inquiryType,message,channel,campaign,adContent,keyword}=payload;
      if(!name || !phone || payload.consent!==true)throw new Error("필수 입력 또는 개인정보 동의를 확인해 주세요.");
      body={name,phone,inquiryType,message,channel,campaign,adContent,keyword};
    }
    // 'no-cors' gives an opaque browser response: request dispatch only,
    // NEVER verified spreadsheet storage. The UI must not claim it is confirmed.
    await fetch(SCRIPT_URL,{
      method:"POST",mode:"no-cors",
      headers:{"Content-Type":"text/plain;charset=utf-8"},
      body:JSON.stringify(body)
    });
    return {ok:true,confirmed:false};
  }
  window.bizstorySubmitLead=send;
  window.bizstoryTrackSearch=(payload)=>send(payload).catch(error=>console.debug("업종 검색 기록 전송 실패",error.message));
})();