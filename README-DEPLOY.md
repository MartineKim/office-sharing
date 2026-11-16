# BizStory 배포 및 운영 확인

- Cloudflare Pages GitHub 자동 배포에서 `functions/api/lead.js`를 /api/lead로 배포해야 합니다. 이 API가 활성화되지 않은 환경에서는 상담 폼에서 오류가 나고 입력내용은 보존됩니다.
- 기존 Apps Script URL은 Cloudflare Function 기본값으로 유지했습니다. 필요하면 Cloudflare 환경변수 `BIZSTORY_APPS_SCRIPT_URL`로 덮어쓰세요.
- 시트 저장 확인은 Apps Script가 `{"ok":true}` 또는 `{"success":true}` 또는 `{"status":"success"}`를 반환할 때만 표시합니다. 일반적인 HTTP 200은 전송 성공만 의미합니다. GAS 로직과 시트 기록 실제 검증을 권장합니다.
- 허니팟과 입력 검증이 추가되었지만 강한 스팸 보호가 필요하면 Turnstile 등 추가 설정이 필요합니다.
- 개인정보 처리방침은 추가되었지만 실제 사업자/담당자 연락처, 위탁·국외이전 사항, Google Sheets 저장 기간, 삭제 작업은 운영자가 최종 보완해야 합니다.
- 기존 Tailwind CDN은 유지했습니다. 생산용 CSS 빌드는 별도 배포 변경 후 전환해야 디자인 깨짐을 피할 수 있습니다.
- 더브릿지플러스 저장 폴더 및 구버전 `industry`는 현재 main의 파일 트리에서 제거합니다. 공개 Git 커밋 이력 전체에는 원본이 남아 있으므로 추후 history rewrite 필요 여부를 별도 검토하세요.
- 공식 도메인 `https://k-bizstory.co.kr`과 Cloudflare Pages 연결, 카카오 채널 URL 및 오픈 후 사업장 주소를 확인하세요.
