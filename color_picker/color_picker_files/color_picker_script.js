/*
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
The NoGray Web Applications are released under the GNU General Public License;
however, you cannot modify the credits on top of each file nor remove them. 

If you would like to use the NoGray Web Applications in any commercial product,
you must get a written permission from the NoGray first. Failure to do so
can result in legal actions. 

For more details about the GNU General Public License, please go to
http://www.gnu.org/copyleft/gpl.html

Note: YOU CANNOT SELL THIS LIBRARY FOR ANY REASON EITHER AS STAND ALONE PRODUCT
	OR AS A PART OF ANOTHER PACKAGE WITHOUR A WRITTEN PERMISSION FROM THE NOGRAY
	
Some files may contain a link to the NoGray.com, we ask you not to remove the links
to help spread the word of our products.
	
This file is designed and written by: Wesam Saif
Contact: admin@nogray.com
Support: support@nogray.com
URL: http://www.nogray.com
Support URL: http://www.nogray.com/phpbb/

For customization and advance editing, please contact us at sales@nogray.com

++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
*/

// setting global variables
// cur_color is a color type for color.js and it holds the current color value
var cur_color = new Color("FF0000", "hex");
// pre_color is a color type and it holds the previous color value
var pre_color = new Color("FF0000", "hex");
// holder_color hold the current color value while it's desaturated 
var holder_color = new Color("FF0000", "hex"); 

// h_range is a Range type from types.js to set the hue range
var h_range = new Range(0, 359);
// sl_range is a Range type to set the saturation and lightness ranges
var sl_range = new Range(0, 100);
// clr_range is a Range type to set the colors range
var clr_range = new Range(0, 255);

// hue_point is a Point type from types.js to define the top left corner of the hue rectangle
var hue_point = new Point(0,0);
// spec_point is a Point type to define the top left corner of the spectrum rectangle
var spec_point = new Point(0,0);

// hue_rect is a Rectangle type from types.js to define the hue rectangle
var hue_rect = new Rectangle(hue_point,0,0);
// spect_rect is a Rectangle type to define the spectrum rectangle
var spect_rect = new Rectangle(spec_point,0,0);

// live ranges for hsl based on the actual elements and positions
var h_slider_range = new Range(0,0);
var s_slider_range = new Range(0,0);
var l_slider_range = new Range(0,0);

// initiating the picker
// the current color and previous color can be passed using the _GET variable
// for example http://domain.com/picker.html?pre_color=ff0000&cur_color=ff0000
// will set the prvious color and the current color to red.
function ini_picker(){
	
	make_NoGray(_obj('ng_cp_hue_color_area'), _obj('ng_cp_spectrum_img'), _obj('ng_cp_spectrum_arrows'), _obj('ng_cp_circle'));
	
	if(is_IE){
		var top_hue = 15;
		var left_hue = 0;
	}
	else if(is_Opera){
		var top_hue = 0;
		var left_hue = 15;
	}
	else {
		var top_hue = 0;
		var left_hue = 15;
	}
	
	// setting the actual values of the objects
	
	hue_point = new Point(_obj('ng_cp_hue_color_area').getLeftOffset()+left_hue-15,_obj('ng_cp_hue_color_area').getTopOffset()-top_hue-15);
	spec_point = new Point(_obj('ng_cp_spectrum_img').getLeftOffset(),_obj('ng_cp_spectrum_img').getTopOffset()-1);

	
	hue_rect = new Rectangle(hue_point, _obj('ng_cp_hue_color_area').getWidth(), _obj('ng_cp_hue_color_area').getHeight());
	spect_rect = new Rectangle(spec_point, 0, _obj('ng_cp_spectrum_img').getHeight()-6);
	
	
	
	
	// setting the actual sliders values
	h_slider_range = new Range(spect_rect.bottomRightCorner.y, spec_point.y);
	s_slider_range = new Range(hue_rect.topLeftCorner.x, hue_rect.topRightCorner.x);
	l_slider_range = new Range(hue_rect.topLeftCorner.y, hue_rect.bottomLeftCorner.y);
	
	// positioning the arrow and circle
	_obj('ng_cp_spectrum_arrows').style.top = (spect_rect.bottomRightCorner.y) + "px";
	_obj('ng_cp_circle').style.top = (hue_rect.topRightCorner.y) + "px";
	_obj('ng_cp_circle').style.left = (hue_rect.topRightCorner.x) + "px";
	
	// making the arrow and circle dragable
	_obj('ng_cp_spectrum_arrows').makeDragable(_obj('ng_cp_spectrum_arrows'),
				"setHueSlider(this.y)",
				"","",true,false,spect_rect);
				
	_obj('ng_cp_circle').makeDragable(_obj('ng_cp_circle'),
				"setSLSlider(this.x, this.y)",
				"","",true,false,hue_rect);
	
	
	// assigning events to the picker parts	
	_obj('ng_cp_des_check').onclick=des_color;
	_obj('ng_cp_invert_check').onclick=invrt_color;
	_obj('ng_cp_websafe_check').onclick=show_websafe;
	
	_obj('ng_cp_spectrum_img').onclick=move_spect_slider;
	_obj('ng_cp_hue_color_area').onclick=move_hue_circle;
	_obj('ng_cp_previous_preview').onclick=function(){cur_color.setValuesFromColor(pre_color); setColors(); check_hue_value(); check_sat_lit_value();}
	
	_obj('ng_cp_h_selector_val').onchange=check_hue_value;
	_obj('ng_cp_s_selector_val').onchange=check_sat_lit_value;
	_obj('ng_cp_l_selector_val').onchange=check_sat_lit_value;
	
	_obj('ng_cp_r_selector_val').onchange=check_color_value;
	_obj('ng_cp_g_selector_val').onchange=check_color_value;
	_obj('ng_cp_b_selector_val').onchange=check_color_value;
	
	_obj('ng_cp_hex_val').onchange=check_hex_value;
	
	_obj('ok_button').onclick=function(){opener.fillColorValue(cur_color.hex); window.close();}
	_obj('cancel_button').onclick=function(){window.close();}
	
	// getting the current and previous color values from the URL
	try {
		if (_GET['pre_color'].toString() != ""){
			pre_color = new Color(_GET['pre_color'], "hex");
			_obj('ng_cp_previous_preview').style.background="#"+pre_color.hex;
		}
	}
	catch(e){
		var foo = 0;
	}
	try {
		if (_GET['cur_color'].toString() != ""){
			cur_color = new Color(_GET['cur_color'], "hex");
			holder_color = new Color(_GET['cur_color'], "hex");
			_obj('ng_cp_h_selector_val').value = cur_color.h;
			_obj('ng_cp_s_selector_val').value = cur_color.s;
			_obj('ng_cp_l_selector_val').value = cur_color.l;
			check_hue_value();
			check_sat_lit_value();
			_obj('ng_cp_current_preview').style.background="#"+cur_color.hex;
		}
	}
	catch(e){
		var foo = 0;
	}
}

// setting the Hue slider and value
function setHueSlider(Y){
	var h = Math.round(h_slider_range.rangeValueToRange(Y, h_range));
	cur_color = new Color(h+","+cur_color.s+","+cur_color.l, "HSL")
	setColors();
}

// setting the Saturation and Lightness slider and values
function setSLSlider(X, Y){
	if (X.toString() == "") var s = cur_color.s;
	else var s = Math.round(s_slider_range.rangeValueToRange(X, sl_range));
	
	if (Y.toString() == "") l = cur_color.l;
	else var l = 100 - Math.round(l_slider_range.rangeValueToRange(Y, sl_range));

	cur_color = new Color(cur_color.h+","+s+","+l, "HSL");
	setColors();
}

// assiging the colors
function setColors(){
	if (_obj('ng_cp_websafe_check').checked) cur_color.toWebSafe();
	
	_obj('ng_cp_h_selector_val').value = cur_color.h;
	_obj('ng_cp_s_selector_val').value = cur_color.s;
	_obj('ng_cp_l_selector_val').value = cur_color.l;
	
	_obj('ng_cp_r_selector_val').value = cur_color.r;
	_obj('ng_cp_g_selector_val').value = cur_color.g;
	_obj('ng_cp_b_selector_val').value = cur_color.b;
	
	_obj('ng_cp_hex_val').value = cur_color.hex;
	
	var h_clr = new Color(cur_color.h+",100,100", "hsl");
	
	_obj('ng_cp_hue_color_area').style.backgroundColor = "#"+h_clr.hex;
	_obj('ng_cp_current_preview').style.backgroundColor = "#"+cur_color.hex;
	
	opener.fillColorValue(cur_color.hex);
}

// desaturate the color
function des_color(){
	if (_obj('ng_cp_des_check').checked) {
		holder_color.setValuesFromColor(cur_color);
		cur_color.Desaturate();
	}
	else {
		cur_color.setValuesFromColor(holder_color);
	}
	setColors();
	check_hue_value();
	check_sat_lit_value();
	
}

// invert the color
function invrt_color(){
	cur_color.Invert();
	setColors();
	check_hue_value();
	check_sat_lit_value();
}

// moving the arrow in the user clicked on the spectrum
function move_spect_slider(evt){
	if (is_IE) evt = event;
	var Y = evt.clientY;
	if (Y > h_slider_range.start) Y = h_slider_range.start;
	else if(Y < h_slider_range.end) Y = h_slider_range.end;
	
	_obj('ng_cp_spectrum_arrows').style.top = (Y-4)+"px";
	
	setHueSlider(Y);
}

// moving the circle if the user clicked on the hue square
function move_hue_circle(evt){
	if (is_IE) evt = event;
	var Y = evt.clientY;
	var X = evt.clientX;
	
	if (X < s_slider_range.start) X = s_slider_range.start;
	else if(X > s_slider_range.end+15) X = s_slider_range.end;
	
	if (Y < l_slider_range.start) Y = l_slider_range.start;
	else if(Y > l_slider_range.end+15) Y = l_slider_range.end;
	
	_obj('ng_cp_circle').style.top = (Y-15)+"px";
	_obj('ng_cp_circle').style.left = (X-15)+"px";
	
	setSLSlider(X, Y)
}

// checking the hue value from the user input
function check_hue_value(){
	var h = _obj('ng_cp_h_selector_val').value;
	if (!h.inString("0123456789")) h = h_range.start;
	if(h > h_range.end) h = h_range.end;
	else if(h < h_range.start) h = h_range.start;
	if (h.isEmpty()) h = h_range.start;
	
	var top = h_range.rangeValueToRange(h, h_slider_range);
	_obj('ng_cp_spectrum_arrows').style.top = (top-4)+"px";
	setHueSlider(top);
}

// checking the saturation and lightness values from the user input
function check_sat_lit_value(){
	var s = _obj('ng_cp_s_selector_val').value;
	if (!s.inString("0123456789")) s = s_range.start;
	if(s > sl_range.end) s = sl_range.end;
	else if(s < sl_range.start) s = sl_range.start;
	if (s.isEmpty()) s = sl_range.start;
	
	var left = sl_range.rangeValueToRange(s, s_slider_range);
	_obj('ng_cp_circle').style.left = (left)+"px";
	
	var l = _obj('ng_cp_l_selector_val').value;
	if (!l.inString("0123456789")) l = l_range.start;
	if(l > sl_range.end) l = sl_range.end;
	else if(l < sl_range.start) l = sl_range.start;
	if (l.isEmpty()) l = sl_range.start;
	var top = sl_range.rangeValueToRange(100-l, l_slider_range);
	_obj('ng_cp_circle').style.top = (top)+"px";
	
	setSLSlider(left,top);
}

// checking the colors value from the user input
function check_color_value(){
	var r = _obj('ng_cp_r_selector_val').value;
	if (!r.inString("0123456789")) r = clr_range.start;
	if(r > clr_range.end) r = clr_range.end;
	else if(r < clr_range.start) r = clr_range.start;
	if (r.isEmpty()) r = clr_range.start;
	
	var g = _obj('ng_cp_g_selector_val').value;
	if (!g.inString("0123456789")) g = clr_range.start;
	if(g > clr_range.end) g = clr_range.end;
	else if(g < clr_range.start) g = clr_range.start;
	if (g.isEmpty()) g = clr_range.start;
	
	var b = _obj('ng_cp_b_selector_val').value;
	if (!b.inString("0123456789")) b = clr_range.start;
	if(b > clr_range.end) b = clr_range.end;
	else if(b < clr_range.start) b = clr_range.start;
	if (b.isEmpty()) b = clr_range.start;
	
	cur_color = new Color(r+","+g+","+b,"rgb");

	_obj('ng_cp_h_selector_val').value = cur_color.h;
	_obj('ng_cp_s_selector_val').value = cur_color.s;
	_obj('ng_cp_l_selector_val').value = cur_color.l;
	
	check_hue_value();
	check_sat_lit_value();
}

// checking the hex value from the user input
function check_hex_value(){
	var hex = _obj('ng_cp_hex_val').value;
	if (!hex.inString("0123456789abcdefABCDEF")) return false;
	if (hex.length < 6) return false;
	
	cur_color = new Color(hex, "hex");
	
	_obj('ng_cp_h_selector_val').value = cur_color.h;
	_obj('ng_cp_s_selector_val').value = cur_color.s;
	_obj('ng_cp_l_selector_val').value = cur_color.l;
	
	check_hue_value();
	check_sat_lit_value();
}


function show_websafe(){
	if (_obj('ng_cp_websafe_check').checked){
		holder_color.setValuesFromColor(cur_color);
		_obj('ng_cp_spectrum_img').src = "images/side_slider_ws.jpg";
	}
	else {
		cur_color.setValuesFromColor(holder_color);
		_obj('ng_cp_spectrum_img').src = "images/side_slider.jpg";
	}
	setColors();
	check_hue_value();
	check_sat_lit_value();
}
