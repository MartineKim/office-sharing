
var type = 'cpa';
var host = "";
var urlParams = new URLSearchParams(window.location.search);
var userId = urlParams.get('userId'); // 오퍼월에서 넘겨준 값
var cid = urlParams.get('cid');
const date = new Date();
const currentTime = date.getTime();
let item_sp = [];
var items_dc = new Array();
var storedCid;
var storedUserId;
var storedProId;
var accessTime;
var eventName;
console.log("-------------------------------------");

var inflowAddress = window.location.href;

message_log(inflowAddress, userId, cid);

if(userId != null && cid != null && type != ""){
	sessionStorage.setItem('cid', cid);
	sessionStorage.setItem('userId', userId);
	sessionStorage.setItem('accessTime', currentTime);

	if(type == "cps"){
		if(host == "CAFE24") {
			var curURL_dc = window.location.href; //현재 URL
			var pathSegments = curURL_dc.split("/");
			var proId = pathSegments[5];
			sessionStorage.setItem('proId', proId);
		} else if (host == "MS"){
			var branduid = urlParams.get('branduid');
			sessionStorage.setItem('proId', branduid);
		} else if (host == "SHOPBY"){
			var productNo = urlParams.get('productNo');
			sessionStorage.setItem('proId', productNo);
		}
	}

	storedCid = cid;
	storedUserId = userId;
	eventName = 'view';

	message_history();
}

function dcampconv_sp_1601 (event, items, adType){
// console.log("items : " ,items);
	storedCid = sessionStorage.getItem('cid');
	storedUserId = sessionStorage.getItem('userId');
	accessTime = sessionStorage.getItem('accessTime');

	if(storedCid != null && storedUserId != null){
		if(event != "purchase_n"){
			if(adType == 'cpa') {
				item_sp = JSON.stringify([items]);
			} else if (adType == 'cps') {
				storedProId = sessionStorage.getItem('proId');

				var proIds = items.map(item => item.id);
				if(proIds.includes(storedProId)){
					item_sp = "[" + items
						.filter(item => item.id === storedProId) 
						.map(item => JSON.stringify(item))
						.join(',') + "]"; 
				}
			}
			 api_call (storedCid, storedUserId, item_sp, event);

		} else {
			//샵바이 네이버페이
			if(message_page_type == "v"){ 
				item_sp = "[" + items
						.map(item => JSON.stringify(item)) 
						.join(',') + "]";
				
			} else if (message_page_type == "b") {
				storedProId = sessionStorage.getItem('proId');

				var proIds = items.map(item => item.id);
				if(proIds.includes(storedProId)){
					item_sp = "[" + items
						.filter(item => item.id === storedProId) 
						.map(item => JSON.stringify(item))
						.join(',') + "]"; 
				}
			}

			eventName = event; 
			message_history();
		}
	}
}

// skp api 호출
function api_call (storedCid, storedUserId, items, event){
	const data = {
	  adkey: "82662a1e609035fb51e1b675112c16f87be42b01833341f50181054e2def31b5",
	  cid: storedCid,
	  userid: storedUserId,
	  adNum: "1601",
	  item : items,
	  event_name : event
	};
	const url = "https://sconv.digitalcamp.co.kr/skp/skp_api_call.php";
	fetch(url, {
	  method: "POST", // HTTP 메서드
	  headers: {
		"Content-Type": "application/json; charset=UTF-8" 
	  },
	  body: JSON.stringify(data) 
	})
	  .then(response => {
		if (!response.ok) {
		  throw new Error(`${response.status}`);
		}
		return response.json(); 
	  })
	  .then(data => {
		console.log("성공:", data);
	  })
	  .catch(error => {
		console.log("오류:", error);
	  });	
}

// history DB 저장
function message_history(){
	const data = {
	  adkey: "82662a1e609035fb51e1b675112c16f87be42b01833341f50181054e2def31b5",
	  cid: storedCid,
	  userid: storedUserId,
	  adNum: "1601",
	  item : JSON.stringify(item_sp),
	  event_name : eventName
	};

	const url = "https://sconv.digitalcamp.co.kr/skp/skp_message_history.php";
	fetch(url, {
	  method: "POST", // HTTP 메서드
	  headers: {
		"Content-Type": "application/json; charset=UTF-8" 
	  },
	  body: JSON.stringify(data) 
	})
	  .then(response => {
		if (!response.ok) {
		  throw new Error(`HTTP error! status: ${response.status}`);
		}
		return response.json(); 
	  })
	  .then(data => {
		console.log("성공:", data);
	  })
	  .catch(error => {
		console.log("오류:", error);
	  });
}

// 네이버페이 주문 (카페24)

window.addEventListener("load", function() {
	if(host == "CAFE24"){
		var naverElement_dc = document.getElementById('dcamp_btn');
	//	var naverElement_class_dc = document.getElementsByClassName('gColumn');
		if(naverElement_dc) { 
			naverElement_dc.addEventListener('click', function(event) {
				storedCid = sessionStorage.getItem('cid');
				storedUserId = sessionStorage.getItem('userId');
				storedProId = sessionStorage.getItem('proId');

				if(storedUserId != null && storedCid != null){
					var items_naver_dc = new Array();
					eventName = "purchase_n";

					if(message_page_type == "v"){ 
						//상품상세
						var proURL_dc = window.location.href; 
						var proPathSegments = proURL_dc.split("/");
						var naver_proId = proPathSegments[5]; 

						if(storedProId == naver_proId){
							var iteminfo_naver;

							var basicProduct = document.querySelectorAll('.option_products');
							if(basicProduct.length > 0 && basicProduct[0].children.length > 0){
								var proQuantity = 0;
								var proPrice = 0;
								var proName;
								var proQuantityElement;
								var proPriceElement

								var optiontElements = document.querySelectorAll('.option_product, .option_product ');
								try{
									optiontElements.forEach(function(basicElement) {
										proName = basicElement.querySelector('.product').innerText;
										proQuantityElement = basicElement.querySelector('input[name="quantity_opt[]"]');
										proQuantity = parseInt(proQuantityElement.value);
										proPriceElement = basicElement.querySelector('input.option_box_price');
										proPrice = parseInt(proPriceElement.value);
										try{ 
											 items_naver_dc.push({'id': storedProId, 'name' : proName, 'quantity' : proQuantity, 'price' : (proPrice / proQuantity)});
										}catch(e){}
									});

									item_sp = "[" + items_naver_dc
											.map(item => JSON.stringify(item)) 
											.join(',') + "]"; 

								}catch(e){}
							} else {
								var totalProducts = document.getElementById('totalProducts');
								var productName = totalProducts.querySelector('td').textContent.trim();
								var productQuantity = totalProducts.querySelector('input[name="quantity_opt[]"]').value;
								var productPrice = totalProducts.querySelector('.option_box_price').value;
								productPrice = productPrice / productQuantity;

								iteminfo_naver = {'id': storedProId, 'name' : productName, 'quantity' : productQuantity, 'price' : productPrice};
								item_sp = JSON.stringify([iteminfo_naver]);
							}

							message_history();
						}
					} else if (message_page_type == "b"){ 
						// 장바구니
						document.querySelectorAll('input[id^="basket_chk_id_"]:checked').forEach(input => {
							const parentRow = input.closest('tr'); 
							if (!parentRow) return;

							const productLink = parentRow.querySelector('.name a'); 
							const optionElement = parentRow.querySelector('.xans-record-'); 
							let proNameValue;

							if (productLink) {
								const href = productLink.getAttribute('href'); 
								const paths = href.split('/'); 
								if(paths[3] == storedProId){
									if(optionElement != null){
										let strongElement = optionElement.querySelector('strong');
										if (strongElement) {
											let optionText = optionElement.textContent.replace(strongElement.textContent, '').trim();
											proNameValue = `${strongElement.textContent.trim()} ${optionText}`;
										 }
									 } else {
										proNameValue = productLink.textContent.trim();
									 }
									let quantityInput = parentRow.querySelector('.right input[id^="quantity_id_"]');
									let rightTd = parentRow.querySelector('.right');
									let nextTd = rightTd ? rightTd.nextElementSibling : null;
									let nextStrongElement = nextTd ? nextTd.querySelector('strong') : null;
									let priceValue;
									let quantityValue;

									if (quantityInput && nextStrongElement) {
										priceValue = parseInt(nextStrongElement.textContent.trim().replace(/[^\d]/g, ''), 10); 
										quantityValue = parseInt(quantityInput.value, 10);
									}

									items_naver_dc.push({'id': storedProId, 'name' : proNameValue, 'quantity' : quantityValue, 'price' : (priceValue / quantityValue)});
								}	
							}
						});

						item_sp = "[" + items_naver_dc
								.map(item => JSON.stringify(item)) 
								.join(',') + "]"; 

						message_history();
					}
				}
			});
		}
	} else if (host == "MS"){
	var naverElement_dc = document.getElementById('dcamp_btn');
		if(naverElement_dc) { 
			naverElement_dc.addEventListener('click', function(event) {
				storedCid = sessionStorage.getItem('cid');
				storedUserId = sessionStorage.getItem('userId');
				storedProId = sessionStorage.getItem('proId');

				if(storedUserId != null && storedCid != null){
					var items_naver_dc = new Array();
					eventName = "purchase_n";

					if(message_page_type == "v"){ 
						branduid = urlParams.get('branduid');
						if(storedProId == branduid){
							var iteminfo_naver;
							var optElements = document.getElementById("MK_innerOptWrap");
							var basicElements = optElements.querySelectorAll('[id^="basic_"]');
							
							basicElements.forEach((element, index) => {
								var mk_productName = element.querySelector(".MK_p-name").textContent.trim();
								var amountValue = parseInt(element.querySelector('input[name="amount[]"]').value, 10);
								var priceText = parseInt(element.querySelector('[id^="MK_p_price_basic_"]').textContent.trim().replace(/[^\d]/g, ''), 10);

								items_naver_dc.push({'id': storedProId, 'name' : mk_productName, 'quantity' : amountValue, 'price' : (priceText / amountValue)});
							});
						}

					} else if (message_page_type == "b"){
						var tbody_dc = document.querySelectorAll('tbody');
						tbody_dc.forEach(function(tbody) {
							var branduidElements  = tbody.querySelectorAll('input[type="hidden"][name="branduid"]');
							branduidElements.forEach(function(branduidElement) {
								var basket_proid = branduidElement.value;
								if(basket_proid == storedProId){
									var brandname; var orgamount; var price_sell;
									var productElement = branduidElement.nextElementSibling;
									while (productElement) {
										if (productElement.getAttribute('name') === 'product_brandname') {
											brandname = productElement.value;  
										} else if (productElement.getAttribute('name') === 'orgamount') {
											orgamount = parseInt(productElement.value, 10);  
										} else if (productElement.tagName === 'TR') {
											var optDiv = productElement.querySelector('td div.tb-opt');
											if (optDiv) {
												var optElement = optDiv.querySelector('.opt_dd'); 
												if (optElement && optElement.textContent.trim() !== "") {
													brandname += " (" + optElement.textContent.trim() + ")";
												}
											}

											var priceDiv = productElement.querySelector('td div.tb-bold.tb-price');
											var priceElement = priceDiv.querySelector('[class*="MK_price_sell"]');
											price_sell = parseInt(priceElement.textContent.trim().replace(/[^\d]/g, ''), 10); 
											break;
										}
										productElement = productElement.nextElementSibling;
									}

									items_naver_dc.push({'id': storedProId, 'name' : brandname, 'quantity' : orgamount, 'price' : (price_sell / orgamount)});
								}
							});
						});
					}

					item_sp = "[" + items_naver_dc
							.map(item => JSON.stringify(item)) 
							.join(',') + "]"; 

					message_history();
				}
			});
		}
	} 
});

function message_log(inflowAddress, userId, cid){
	const data = {
	  adkey: "82662a1e609035fb51e1b675112c16f87be42b01833341f50181054e2def31b5",
	  cid: cid,
	  userid: userId,
	  address : inflowAddress
	};

	const url = "https://sconv.digitalcamp.co.kr/skp/skp_log.php";
	fetch(url, {
	  method: "POST", // HTTP 메서드
	  headers: {
		"Content-Type": "application/json; charset=UTF-8" 
	  },
	  body: JSON.stringify(data) 
	})
	  .then(response => {
		if (!response.ok) {
		  throw new Error('서버 응답 오류');
		}
	  })
	  .catch(error => {
		console.error('에러 발생:', error);
	  });
}

