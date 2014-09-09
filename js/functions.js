function check_form(){
    var cid = document.form1.cid.value;    
    var date_surv = document.form1.date_surv.value;    
        if(cid.length < 1){
            alert("โปรดระบุเลขบัตรประชาชน");
            document.form1.cid.focus();
            return false;
        }
        else if(date_surv.length < 1){
            alert("โปรดระบุวันที่สำรวจข้อมูล");
            document.form1.date_surv.focus();
            return false;
        }
        else {
            document.form1.Submit.disabled=true;
            return true;
        }
}
var xmlHttp;

function createXMLHttpRequest() {
    if (window.ActiveXObject) {
    xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	 } 
	else if (window.XMLHttpRequest) {
 	 xmlHttp = new XMLHttpRequest();
	 }
}
function adjustWindow(){
 var screenHeight=window.innerHeight;
 var screenWidth=screen.availWidth;
 var mHeight=(screenHeight-50)+'px';
 var yHeight=(screenHeight-56)+'px';
 document.getElementById("pright").style.height=mHeight;
 document.getElementById("pleft").style.height=yHeight;
}
