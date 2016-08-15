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

// This is a new JS type for colors
// once the color is assign, the object
// will have access to the Red, Green, and Blue
// or the Hus, Saturation, and Lightness,
// or the Hexadecimal color value
// Type should be either 
// hex, rgb, hsl, or color
// color is the color value depending on the type
// examples for white
// hex example "FFFFFF"
// rgb example "255,255,255"
// hsl example "0,0,100"
// color example "white"
// for type color, only 16 colors are supported
// and the type will be switched to hexadecimal
// supported color type are aqua, black, blue, fuchsia, gray,
// green, lime, maroon, navy, olive, purple, red, silver,
// teal, white, and yellow


// this is the Color type constructor
function Color(Clr, Type){
	this.RGBtoHex = RGBtoHex;
	this.RGBtoHSL = RGBtoHSL;
	this.HSLtoRGB = HSLtoRGB;
	this.HextoRGB = HextoRGB;
	this.Invert = Invert;
	this.Desaturate = Desaturate;
	this.toWebSafe = toWebSafe;
	this.setValuesFromColor=setValuesFromColor;
	
	Type = Type.toLowerCase();
	
	if (Type == "color"){
		Clr = Clr.toLowerCase();
		if (Clr == "aqua") Clr = "00FFFF";
		else if(Clr == "black") Clr = "000000";
		else if(Clr == "blue") Clr = "0000FF";
		else if(Clr == "fuchsia") Clr = "FF00FF";
		else if(Clr == "gray") Clr = "808080";
		else if(Clr == "green") Clr = "008000";
		else if(Clr == "lime") Clr = "00FF00";
		else if(Clr == "maroon") Clr = "800000";
		else if(Clr == "navy") Clr = "000080";
		else if(Clr == "olive") Clr = "808000";
		else if (Clr == "purple") Clr = "800080";
		else if(Clr == "red") Clr = "FF0000";
		else if(Clr == "silver") Clr = "C0C0C0";
		else if(Clr == "teal") Clr = "008080";
		else if(Clr == "white") Clr = "FFFFFF";
		else if(Clr == "yellow") Clr = "FFFF00";
		else Clr = "000000";
		
		Type = "hex";
	}
	
	if(Type == "rgb"){
		var rgb_arr = Clr.split(",");
		this.r = parseInt(rgb_arr[0]);
		this.g = parseInt(rgb_arr[1]);
		this.b = parseInt(rgb_arr[2]);
		
		this.RGBtoHex();

		this.RGBtoHSL();
	}
	else if(Type == "hsl"){
		var hsl_arr = Clr.split(",");
		this.h = parseInt(hsl_arr[0]);
		this.s = parseInt(hsl_arr[1]);
		this.l = parseInt(hsl_arr[2]);
		
		this.HSLtoRGB();
		
		this.RGBtoHex();
	}
	else {
		this.hex = Clr;
		
		this.HextoRGB();
		
		this.RGBtoHSL();
	}	
	
	if (this.h > 359) this.h = 359;
	if (this.h < 0) this.h = 0;
	if (this.s > 100) this.s = 100;
	if (this.s < 0) this.s = 0;
	if (this.l > 100) this.l = 100;
	if (this.l < 0) this.l = 0;
	
	if (this.r > 255) this.r = 255;
	if (this.r < 0) this.r = 0;
	if (this.g > 255) this.g = 255;
	if (this.g < 0) this.g = 0;
	if (this.b > 255) this.b = 255;
	if (this.b < 0) this.b = 0;
}

// function to convert RGB to Hex
function RGBtoHex(){
	var rHex_base = this.r / 16;
	var rHex_rem = this.r % 16;
	
	var rHex = makeHex(rHex_base - (rHex_rem / 16), rHex_rem);
	
	var gHex_base = this.g / 16;
	var gHex_rem = this.g % 16;
	
	var gHex = makeHex(gHex_base - (gHex_rem / 16), gHex_rem);
	
	var bHex_base = this.b / 16;
	var bHex_rem = this.b % 16;
	
	var bHex = makeHex(bHex_base - (bHex_rem / 16), bHex_rem);
	
	this.hex = rHex.toString() + gHex.toString() + bHex.toString();
}

// function to make Hex values from Integers
function makeHex(val1, val2){
	var part1 = 0;
	var part2 = 0;
	
	var arr_letters = new Array();
	arr_letters[15] = "F";
	arr_letters[14] = "E";
	arr_letters[13] = "D";
	arr_letters[12] = "C";
	arr_letters[11] = "B";
	arr_letters[10] = "A";
	
	if (val1 >= 10) part1 = arr_letters[val1];
	else part1 = val1;

	if (val2 >= 10) part2 = arr_letters[val2];
	else part2 = val2;
	
	return  part1.toString() + part2.toString();
	
}

// this function will convert the RGB to HSL
function RGBtoHSL(){
	var r = this.r/255;
	var g = this.g/255;
	var b = this.b/255;
	var H = 0;

	var mn = Math.min(r, g, b);
	var mx = Math.max(r, g, b);
	
	var mnx = mx - mn;
	
	this.l = Math.round(mx * 100);

	if (mnx == 0){
		this.h = 0;
		this.s = 0;
	}
	else {
		this.s = Math.round((mnx/mx) * 100);
		
		del_R = (((mx - r) / 6) + (mnx / 2)) / mnx;
		del_G = (((mx - g) / 6) + (mnx / 2)) / mnx;
		del_B = (((mx - b) / 6) + (mnx / 2)) / mnx;
			
		if      (r == mx) this.h = del_B - del_G;
		else if (g == mx) this.h = (1 / 3) + del_R - del_B;
		else if (b == mx) this.h = (2 / 3) + del_G - del_R;
			
		if ( this.h < 0 ) this.h += 1;
		if ( this.h > 1 ) this.h -= 1;
		
		this.h = Math.round(360 * this.h);
	}
}

// this function will convert the HSL to RGB
function HSLtoRGB(){
	var L = Math.round((this.l * 255 )/ 100);
	var S = Math.round((this.s * 255 )/ 100);
	var r=0;
	var b=0;
	var g=0;
	var mr=0;
	var mg=0;
	var mb=0;
	
	if(S == 0){
		this.r = this.g = this.b = L;
	}
	else {
		var t1 = L;
		var t2 = (255-S)*L/255;
		var t3 = this.h%60;
		t3 = (t1-t2)*t3/60;
		
		if (this.h < 60){
			this.r=t1;	this.b=t2;	this.g=t2+t3;
		}
		else if(this.h < 120){
			this.g=t1;	this.b=t2;	this.r=t1-t3;			
		}
		else if(this.h < 180){
			this.g=t1;	this.r=t2;	this.b=t2+t3;
		}
		else if(this.h < 240){
			this.b=t1;	this.r=t2;	this.g=t1-t3;
		}
		else if(this.h < 300){
			this.b=t1;	this.g=t2;	this.r=t2+t3;
		}
		else if(this.h < 360){
			this.r=t1;	this.g=t2;	this.b=t1-t3;
		}
		else {
			this.r = this.g = this.b = 0;
		}
		
	}
	
	this.r = Math.round(this.r);
	this.g = Math.round(this.g);
	this.b = Math.round(this.b);	
}

// this function will convert the Hex to RGB
function HextoRGB(){
	this.r = parseInt(this.hex.substr(0,2).toUpperCase(), 16);
	this.g = parseInt(this.hex.substr(2,2).toUpperCase(), 16);
	this.b = parseInt(this.hex.substr(4,2).toUpperCase(), 16);	
}

// invert the color
function Invert(){
	var r = 255 - this.r;
	var g = 255 - this.g;
	var b = 255 - this.b;
	this.setValuesFromColor(new Color(r+","+g+","+b, "rgb"));
}

// desaturate the color and return the gray scale for it
function Desaturate(){
	var l = Math.round(this.l - ((this.s/200) * this.l));	
	this.setValuesFromColor(new Color(this.h+","+0+","+l, "hsl"));
}

// switch the color to web safe color
function toWebSafe(){
	var r = 0;
	var g = 0;
	var b = 0;
	
	if (this.r > 230) r = 255;
	else if(this.r > 179) r = 204;
	else if(this.r > 128) r = 153;
	else if(this.r > 77) r = 102;
	else if(this.r > 25) r = 51;
	else r = 0;
	
	if (this.g > 230) g = 255;
	else if(this.g > 179) g = 204;
	else if(this.g > 128) g = 153;
	else if(this.g > 77) g = 102;
	else if(this.g > 25) g = 51;
	else g = 0;
	
	if (this.b > 230) b = 255;
	else if(this.b > 179) b = 204;
	else if(this.b > 128) b = 153;
	else if(this.b > 77) b = 102;
	else if(this.b > 25) b = 51;
	else b = 0;
	
	this.setValuesFromColor(new Color(r+","+g+","+b, "rgb"));
}

// set the colors value from another color
function setValuesFromColor(Clr){
	this.hex = Clr.hex;
	this.r = Clr.r;
	this.g = Clr.g;
	this.b = Clr.b;
	this.h = Clr.h;
	this.s = Clr.s;
	this.l = Clr.l;
}
