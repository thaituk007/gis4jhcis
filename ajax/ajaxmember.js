function getXmlHttpRequestObject() {
 if (window.XMLHttpRequest) {
    return new XMLHttpRequest(); //Mozilla, Safari ...
 } else if (window.ActiveXObject) {
    return new ActiveXObject("Microsoft.XMLHTTP"); //IE
 } else {
    alert("Your browser doesn't support the XmlHttpRequest object.");
 }
}

var receiveReq = getXmlHttpRequestObject();

function checkpasswd(pwd, rpwd) {
    if (pwd!=rpwd) {
      document.getElementById("msg11").innerHTML = '<div class="alert alert-danger alert-dismissable"><div align="center"><strong> รหัสผ่านไม่ตรงกัน</strong></div></div>';
      document.form1.repassword.focus(); 
      document.getElementById('txtCaptcha').disabled = true; 	 
      document.getElementById('btnSubmit').disabled = true; 	  
	} else {
      document.getElementById("msg11").innerHTML = '<div class="alert alert-success alert-dismissable"><div align="center"><strong> รหัสผ่านตรงกัน</strong></div></div>';		
      document.getElementById('txtCaptcha').disabled = false
      document.getElementById('btnSubmit').disabled = false;  
    } 
}

function makeRequest(url, param) {
 if (receiveReq.readyState == 4 || receiveReq.readyState == 0) {
   receiveReq.open("POST", url, true);
   receiveReq.onreadystatechange = updatePage;   

   receiveReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
   receiveReq.setRequestHeader("Content-length", param.length);
   receiveReq.setRequestHeader("Connection", "close");

   receiveReq.send(param);
 }   
}