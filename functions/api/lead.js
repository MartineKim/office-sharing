/* Cloudflare Pages Function, /api/lead */
const SCRIPT_URL="https://script.google.com/macros/s/AKfycbw5P-hbWNdaQKDHlD_EJBExLu1Dyoj6CtFOy5Msfzcpb0Dc5rKrxHISRA0g3pY2OZoCjg/exec";
const json=(o,status=200)=>new Response(JSON.stringify(o),{status,headers:{"Content-Type":"application/json; charset=utf-8","Cache-Control":"no-store","X-Content-Type-Options":"nosniff"}});
const str=(v,n)=>typeof v==="string"?v.trim().slice(0,n):"";
export async function onRequestPost({request,env}){
if(request.headers.get("origin")&&request.headers.get("origin")!==new URL(request.url).origin)return json({ok:false,error:"요청 출처 오류"},403);
if(!(request.headers.get("content-type")||"").includes("application/json"))return json({ok:false,error:"요청 형식 오류"},415);
if(Number(request.headers.get("content-length")||0)>8192)return json({ok:false,error:"요청 크기 초과"},413);
let data;try{const raw=await request.text();if(raw.length>8192)throw Error("size");data=JSON.parse(raw)}catch(_){return json({ok:false,error:"잘못된 요청입니다."},400)}
if(!data||typeof data!=="object"||Array.isArray(data))return json({ok:false,error:"잘못된 요청입니다."},400);
let out;
if(data.type==="industrySearch"){out={type:"industrySearch",keyword:str(data.keyword,120),matchedName:str(data.matchedName,120),matchedCode:str(data.matchedCode,32),status:str(data.status,50),channel:str(data.channel,80),campaign:str(data.campaign,100)};if(!out.keyword)return json({ok:false,error:"검색어 누락"},400);}
else{
if(data.website)return json({ok:true,confirmed:false});
const name=str(data.name,100),phone=str(data.phone,32);
if(data.consent!==true)return json({ok:false,error:"개인정보 동의가 필요합니다."},400);
if(name.length<2||!/^[0-9+()\-\s]{8,32}$/.test(phone)||phone.replace(/\D/g,"").length<8)return json({ok:false,error:"이름과 연락처를 확인해 주세요."},400);
out={name,phone,inquiryType:str(data.inquiryType,100),message:str(data.message,2000),channel:str(data.channel,80),campaign:str(data.campaign,100),adContent:str(data.adContent,100),keyword:str(data.keyword,120)};
}
const endpoint=env?.BIZSTORY_APPS_SCRIPT_URL||SCRIPT_URL;
if(!/^https:\/\/script\.google\.com\/macros\/s\/[^/]+\/exec$/.test(endpoint))return json({ok:false,error:"스크립트 주소 설정 오류"},503);
try{
const res=await fetch(endpoint,{method:"POST",headers:{"Content-Type":"text/plain;charset=utf-8"},body:JSON.stringify(out),redirect:"follow"});
const body=await res.text();
if(!res.ok||/^\s*<!doctype html|^\s*<html|script function not found|authorization required|sign in to continue|exception:/im.test(body))return json({ok:false,error:"상담 시스템 응답 오류"},502);
let confirmed=false;try{const result=JSON.parse(body);if(result&&typeof result==="object"){if(result.ok===false||result.success===false||result.status==="error")return json({ok:false,error:"상담 시스템 처리 오류"},502);confirmed=result.ok===true||result.success===true||result.status==="success";}}catch(_){}
return json({ok:true,confirmed});
}catch(e){console.error("BizStory submit upstream error",e?.name||"error");return json({ok:false,error:"상담 시스템 접속 오류"},502)}
}
export function onRequestGet(){return json({ok:false,error:"POST만 허용됩니다."},405)}