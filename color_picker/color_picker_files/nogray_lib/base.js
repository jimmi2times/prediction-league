/*
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
The NoGray JS Library is released under the GNU General Public License;
however, you cannot modify the credits on top of each file nor remove them. 

If you would like to use the NoGray JS Library in any commercial product,
you must get a written permission from the NoGray first. Failure to do so
can result in legal actions. 

For more details about the GNU General Public License, please go to
http://www.gnu.org/copyleft/gpl.html

Note: YOU CANNOT SELL THIS LIBRARY FOR ANY REASON EITHER AS STAND ALONE PRODUCT
	OR AS A PART OF ANOTHER PACKAGE WITHOUR A WRITTEN PERMISSION FROM THE NOGRAY
	
This file is designed and written by: Wesam Saif
Contact: admin@nogray.com
Support: support@nogray.com
URL: http://www.nogray.com
Support URL: http://www.nogray.com/phpbb/

++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
*/

//==================================================================
// General functions to extend javascript objects
//==================================================================

//==================================================================
// General variables and settings
//==================================================================

// checking if the browser is Opera
var is_Opera = (window.navigator.userAgent.search("Opera") != -1);

// checking if the browser is IE
var is_IE = ((window.navigator.userAgent.search("MSIE") != -1) && !is_Opera);


// this string will hold all the libraries methods to run when an
// object is assigned to the NoGray Library
var _functions_loader_txt = "";
var _run_once_on_ini = "";
var _did_add_body = false;

// _GET variable is almost the same as the $_GET variable in PHP

var _GET = new Array();

// attaching new array functions to javascript type Array
Array.prototype.ArrayIndex=ArrayIndex;
Array.prototype.inArray=inArray;

// attaching new string functions to javascript type String
String.prototype.inString=inString;
String.prototype.isUpper=isUpper;
String.prototype.isLower=isLower;
String.prototype.isNumeric=isNumeric;

// attaching general functions to javascript Objects
Object.prototype.isEmpty=isEmpty;

//==================================================================
// Custome methods starts here
//==================================================================

// this function will assign the NoGray functions and methods
// to the object
function make_NoGray(){
	var i=0;
	var Obj;
	if (!_did_add_body){
		Obj = document.body;
		eval(_functions_loader_txt);
		eval(_run_once_on_ini);
		_did_add_body = false;
	}
	for(i=0; i<arguments.length;i++){
		Obj = arguments[i];
		eval(_functions_loader_txt);
	}
}

// function to create HTML elements and make them
// a NoGray objects
function createElement(tag){
	var obj = document.createElement(tag);
	make_NoGray(obj);
	
	return obj;
}

// function to return an object based on the id
function _obj(id){
	return document.getElementById(id);
}

// function to attach events to elements
// to reduce the amount of code when attaching
// events to objects in a script
function addEvent(Eve, Func){
	if(window.addEventListener){
		Eve = Eve.substr(2, Eve.length - 2);
		this.addEventListener(Eve, Func, false);
	} else {
		this.attachEvent(Eve, Func);
	}
}


// This function will check if the provided string
// is in the main string.
// boolean return
function inString(str){
	var inString_i = 0;
	for(inString_i=0; inString_i<this.length; inString_i++){
		if (str.indexOf(this.charAt(inString_i),0) == -1) return false;
	}	
	return true;
}


// function to check if a value is empty
// boolean return
function isEmpty(){
	var str = this.toString();
	var re = false;
	
	if (str == "") re = true;
	if (this == null) re = true;
	if (str == "undefined") re = true;
	return re;
}


// This function will return the array index
// for a corresponding value. If the value is not
// in the array, the function will return -1
function ArrayIndex(val){
	var i = 0;
	for(i=0; i<this.length; i++){
		if(this[i] == val){
			return i;	
		}
	}
	return -1;
}

// This function will check in a value is in
// the array. Boolean return
function inArray(val){
	var i = 0;
	for(i=0; i<this.length; i++){
		if(this[i] == val){
			return true;	
		}
	}
	return false;
}


// This function will check if all the characters
// in the string are upper case
// boolean return
function isUpper(){
	return (this.match(/^([^a-z]\.*[^a-z]*)$/) != null);
}


// This function will check if all the characters
// in the string are lower case
// boolean return
function isLower(){
	return (this.match(/^([^A-Z]\.*[^A-Z]*)$/) != null);
}

// This function will check if all the characters
// in the string are numbers
// boolean return
function isNumeric(){
	return (this.match(/^(-?\d*\.?\d*)$/) != null);	
}

// This function will return a random number
// between the Start and End
function Random(Start, End){
	return Math.round(Math.random() * (End - Start)) + Start;
}

// This function will return a radmon string
// in the length provided in Length
function generateRandomText(Lenght){
	var st = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	var re = "";
	var i = 0;
	for(i=0; i<= Lenght; i++){
		re += st.substr(Random(0, st.length), 1);
	}
	
	return re;
}


// this function will return the outer HTML of the element

function getOuterHtml(){
	if (is_IE) return this.outerHTML;
	var temp = document.createElement("DIV");
	var temp_c = this.cloneNode(true);
	temp.appendChild(temp_c);
	var re = temp.innerHTML.toString();
	delete temp;
	delete temp_c;
	return re;
}

//==================================================================
// General visual functions
//==================================================================


// this function will return the current style of an object
// in run time. The style doesn't have to be initialized in javascript
function getStyle(Property){	
	if (is_IE){
		return eval("this.currentStyle."+Property);
	}
	else {
		Property = fixStyleFormatFF(Property);
		return document.defaultView.getComputedStyle(this, null).getPropertyValue(Property);	
	}
	
}

// this function will fix the style format for Firefox
// in case firefox needs the - instead of the capital letter
// mainly used for the getStyle() function
function fixStyleFormatFF(val){
	var re = "";
	var i = 0;
	for(i=0; i<val.length; i++){
		if (val.charAt(i).isUpper()){
			re += "-"
		}
		re += val.substr(i, 1);
	}
	return re.toLowerCase();
}


// this function will return the height of the object
// as integare
function getHeight(){
	return this.offsetHeight;
}


// this function will return the width of the object
// as integare
function getWidth(){
	return this.offsetWidth;
}


// This function will set the opacity of an object
// Works for IE and firefox and Opera 9.0
function setOpacity(val){
	if (is_IE){
		this.style.filter ="progid:DXImageTransform.Microsoft.BasicImage(Opacity="+val+")";
	}
	else {
		this.style.MozOpacity = val;
	}
	this.style.opacity = val;
}

// This function will set the top and left margin
// of an object in run time.
// mainly used for objects with absolut positing
function setMargins(Tp, Lft){
	this.style.marginTop = Tp + "px";
	this.style.marginLeft = Lft + "px";
}

// This function will return the correct top offset
// for an html objects
function getTopOffset(){
	var add = 0;
	if (is_IE){
		if (this.getStyle("position") != "absolute") add = parseInt(document.body.getStyle("marginTop"));
	}
	var obj = this;
	add += obj.offsetTop;
	
	try {
		while (obj.offsetParent.tagName != "BODY"){
			try {
				add += obj.offsetTop;
				obj = obj.offsetParent;
			}
			catch(e) {
				var foo=0;	
			}
		}
	}
	catch (e){
		var foo = 0;	
	}
	return add;
}

// This function will return the correct left offset
// for an html objects
function getLeftOffset(){
	var add = 0;
	if (is_IE){
		if (this.getStyle("position") != "absolute") add = parseInt(document.body.getStyle("marginLeft"));
	}
	obj = this;
	add += obj.offsetLeft;
	try {
		while (obj.offsetParent.tagName != "BODY"){
			try {
				add += obj.offsetLeft;
				obj = obj.offsetParent;
			}
			catch(e) {
				var foo=0;	
			}
		}
	}
	catch (e){
		var foo = 0;	
	}
	return add;
}


// this function will show the html element
// should be use with hide()
function show(){
	this.style.display == "";
	
	if (this.getStyle("display") == "none"){
		this.style.display = "block";	
	}
}

// this function will hide the html element
// should be used with show()
function hide(){
	this.style.display = "none";	
}



//==================================================================
// Attaching these file methods to HTML elements and objects
//==================================================================

// the following string will hold the functions to run when the 
// object is assigned to the NoGray Libraray.
_functions_loader_txt += "Obj.getStyle=getStyle;\n";
_functions_loader_txt += "Obj.getHeight=getHeight;\n";
_functions_loader_txt += "Obj.getWidth=getWidth;\n";
_functions_loader_txt += "Obj.setOpacity=setOpacity;\n";
_functions_loader_txt += "Obj.setMargins=setMargins;\n";
_functions_loader_txt += "Obj.getTopOffset=getTopOffset;\n";
_functions_loader_txt += "Obj.getLeftOffset=getLeftOffset;\n";
_functions_loader_txt += "Obj.addEvent=addEvent;\n";
_functions_loader_txt += "Obj.show=show;\n";
_functions_loader_txt += "Obj.hide=hide;\n";
_functions_loader_txt += "Obj.getOuterHtml=getOuterHtml;\n";




//==================================================================
// processing the _GET variable from the page URI
//==================================================================
var _uri = location.href;
var _temp_get_arr = _uri.substring(_uri.indexOf('?')+1, _uri.length).split("&");
var _temp_get_arr_1 = new Array();
var _temp_get_val_holder = "";
for(_get_arr_i=0; _get_arr_i<_temp_get_arr.length; _get_arr_i++){
	_temp_get_val_holder = "";
	_temp_get_arr_1 = _temp_get_arr[_get_arr_i].split("=");
	for (_get_arr_j=1; _get_arr_j<_temp_get_arr_1.length; _get_arr_j++){
		if (_get_arr_j > 1) _temp_get_val_holder += "=";
		_temp_get_val_holder += decodeURI(_temp_get_arr_1[_get_arr_j]);
	}
	_GET[decodeURI(_temp_get_arr_1[0])] = _temp_get_val_holder;
}
delete _uri; delete _temp_get_arr; delete _temp_get_arr_1; delete _temp_get_val_holder;

