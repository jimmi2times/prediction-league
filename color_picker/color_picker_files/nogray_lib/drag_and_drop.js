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

// global array to hold the objects that are set
// as draggable
var _drag_objects = new Array();

// variable to hold which object is currently being dragged
var _which_object = "";

// setting the grid width and height
var _grid_width = 20;
var _grid_height = 20;

// saving the current selection to add after dragging
var _cur_selection;

// function to make an object draggable
function makeDragable(Handle, Func, onStart, onEnd, onTop, Grid, Rect){
	make_NoGray(Handle);
	if (Handle.id.isEmpty()) Handle.id = generateRandomText(7);
	if (this.id.isEmpty()) this.id = generateRandomText(7);
	if (typeof Rect != "object") Rect = new Rectangle(new Point(0,0), screen.availWidth - this.getWidth() , screen.availHeight - this.getHeight());	
	if (typeof Func != "string") Func = "";
	if (typeof onStart != "string") onStart = "";
	if (typeof onEnd != "string") onEnd = "";
	if (typeof onTop != "boolean") onTop = true;
	if (typeof Grid != "boolean") Grid = false;
	
	Handle.ondrag= function (evt){
		try {
			evt.preventDefault();
		}
		catch(e){
			return false;	
		}
		return false;
	}
	
	_drag_objects[Handle.id] = new Array(this.id,false,0,0,Func,onTop,Grid,Rect,this.getStyle("zIndex"),onStart,onEnd);
	Handle.onmousedown = _iniDrag;
	Handle.onmouseup= _stopDrag;
}

function _iniDrag(evt){
	if (!is_IE){
		if (evt.preventDefault) evt.preventDefault();
	}
	if (is_IE || is_Opera) _cur_selection = document.selection.createRange();
	
	if (is_IE) evt = event;
	_drag_objects[this.id][1] = true;
	if (is_IE || is_Opera) {
		if (is_IE) var add = 3;
		else var add = 10;
		_drag_objects[this.id][2] = evt.offsetX + add;
		_drag_objects[this.id][3] = evt.offsetY + add;
	}
	else{
		_drag_objects[this.id][2] = window.pageXOffset + evt.clientX - GetLocation(__getNonTextNode(evt.explicitOriginalTarget)).x;
		_drag_objects[this.id][3] = window.pageYOffset + evt.clientY - GetLocation(__getNonTextNode(evt.explicitOriginalTarget)).y;
	}
	_which_object = this.id;
	this.style.cursor="move";
	
	if (_drag_objects[this.id][5]){
		_obj(_drag_objects[this.id][0]).style.zIndex = 10000;	
	}
	
	if (_drag_objects[this.id][9] != ""){
		var func = _fillDragFunc(_drag_objects[this.id][9], _drag_objects[this.id][0]);
		eval(func);
	}
	
	_obj(_drag_objects[this.id][0]).onDragStart;
}

function _stopDrag(){
	_cur_selection = "";
	_drag_objects[this.id][1] = false;
	_drag_objects[this.id][2] = 0;
	_drag_objects[this.id][3] = 0;
	_which_object = "";
	this.style.cursor = "";
	_obj(_drag_objects[this.id][0]).style.zIndex = _drag_objects[this.id][8];
	if (_drag_objects[this.id][10] != ""){
		var func = _fillDragFunc(_drag_objects[this.id][10], _drag_objects[this.id][0]);
		eval(func);
	}
}

function _drag(evt){
	if (!_which_object.isEmpty()){
		if (_drag_objects[_which_object][1]){
			if (is_IE) evt = event;
			if (is_IE || is_Opera) _cur_selection.select();
			
			var lft = evt.clientX - _drag_objects[_which_object][2];
			var tp = evt.clientY - _drag_objects[_which_object][3];
			
			var process_left = true;
			var process_top = true;
			
			if (_drag_objects[_which_object][6]){
				if ((lft % _grid_width) < (_grid_width / 2)) lft = parseInt(_obj(_drag_objects[_which_object][0]).getStyle("left"));
				else lft = parseInt(lft / _grid_width) * _grid_width;
				
				if ((tp % _grid_height) < (_grid_height / 2)) tp = parseInt(_obj(_drag_objects[_which_object][0]).getStyle("top"));
				else tp = parseInt(tp / _grid_height) *  _grid_height;
			}
			
			if (lft > _drag_objects[_which_object][7].topRightCorner.x) process_left = false;
			else if (lft < _drag_objects[_which_object][7].topLeftCorner.x) process_left = false;
			else if(_drag_objects[_which_object][7].topLeftCorner.x == _drag_objects[_which_object][7].topRightCorner.x) process_left = false;
			else process_left = true;
			
			if (tp > _drag_objects[_which_object][7].bottomLeftCorner.y) process_top = false;
			else if (tp < _drag_objects[_which_object][7].topLeftCorner.y) process_top = false;
			else if(_drag_objects[_which_object][7].topLeftCorner.y == _drag_objects[_which_object][7].bottomLeftCorner.y) process_top = false;
			else process_top = true;
			
			if (process_left) _obj(_drag_objects[_which_object][0]).style.left = lft + "px";
			if (process_top) _obj(_drag_objects[_which_object][0]).style.top = tp + "px";
			
			if (_drag_objects[_which_object][4] != ""){
				var func = _fillDragFunc(_drag_objects[_which_object][4], _drag_objects[_which_object][0]);
				eval(func);
			}
		}
	}
}

function _fillDragFunc(val, elemID){
	var func = val.replace(/this.x/g, parseInt(_obj(elemID).getStyle("left")));
	func = func.replace(/this.y/g, parseInt(_obj(elemID).getStyle("top")));
	func = func.replace(/this/g, "_obj('"+elemID+"')");
	
	return func;
}


//==================================================================
// Attaching these file methods to HTML elements and objects
//==================================================================

// the following string will hold the functions to run when the 
// object is assigned to the NoGray Libraray.
_functions_loader_txt += "Obj.makeDragable=makeDragable;\n";
_run_once_on_ini += "document.onmousemove= _drag;\n";



//==================================================================
// function to calculate the offsetX and offsetY for firefox
// found at http://davidlevitt.com/2006/04/04/how-to-implement-offsetx-and-offsety-in-firefox.aspx
//==================================================================

function __getNonTextNode(node) {
    try {
        while (node && node.nodeType != 1) {
            node = node.parentNode;
        }
    }
    catch (ex) {
        node = null;
    }
    return node;
}

 

function GetLocation(el) {
      var c = { x : 0, y : 0 };
      while (el) {
            c.x += el.offsetLeft;
            c.y += el.offsetTop;
            el = el.offsetParent;
      }
      return c;

}


