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

// custom NoGray JS Types
// these types extends JavsScript types
// and are neccessary for some of the
// NoGray JS library files. 

// this is a new object to represent a Point
// each point will have an X and Y values
function Point(x, y){
	this.x = x;
	this.y = y;
	
	this.distanceToPoint = pointToPointDistance;
}

// this function will calculate the distance
// between two points
function pointToPointDistance(pnt){
	return Math.sqrt(Math.pow(pnt.x - this.x, 2) + Math.pow(pnt.y - this.y, 2));
}


// this is a new object to represent a Range
// each Range will have a Start and End values
function Range(start, end){
	this.start = start;
	this.end = end;
	
	this.rangeValueToRange = rangeValueToRange;
}

// this function will change a value from a range
// to equal value in another range
function rangeValueToRange(val, rng){
	return ((val - this.start) * ((rng.end - rng.start)/(this.end - this.start)) + rng.start);	
}

// this is a new object to represent a Stroke
// each Stroke will have a style, width, and color
// styles are the same as the CSS border styles
// e.g. solid, dashed, dotted, etc...
// color should be in hexadecimal value
function Stroke(style, width, color){
	this.style = style;
	this.width = parseInt(width);
	this.color = color.replace("#", "");
}

// a rectangle is a new object that represent a Rectangle
// each ractangle will have the following values
// topLeftCorner - type Point - needed to initiate the Rectangle
// topRightCorner - type Point - calculated automaticlly
// bottomRightCorner - type Point - calculated automaticlly
// bottomLeftCorner - type Point - calculated automaticlly
// width - type Integer - needed to initiate the Rectangle
// height - type Integer - needed to initiate the Rectangle
// area - type Integer - calculated automatically
// preimetet - type Integer - calculated automaticlly
// Obj - HTML Element - is the HTML element that represent the
//       rectange. Will be created when the rectange is drawn
function Rectangle(topLeftCorner, width, height){
	this.topLeftCorner = topLeftCorner;
	this.topRightCorner = new Point(topLeftCorner.x + width, topLeftCorner.y);
	this.bottomLeftCorner = new Point(topLeftCorner.x, topLeftCorner.y + height);
	this.bottomRightCorner = new Point(topLeftCorner.x + width, topLeftCorner.y + height);
	this.width = width;
	this.height = height;
	this.area = width * height;
	this.perimeter = (width * 2) + (height * 2);
	this.Obj = "";
	
	this.draw = drawRectangle;
}

// this function will draw a  rectange
// rectange.draw(Stroke, Fill)
// stroke is a Stroke type
// fill - String - can be a color or none for transparent
// fill can be any CSS value that will go with the background
function drawRectangle(stroke, fill){
	fill = fill.replace("#", "");
	if (fill.length == 6) fill = "#" + fill;
	rect = createElement("DIV");
	rect.style.overflow = "hidden";
	rect.style.position = "absolute";
	rect.style.top = this.topLeftCorner.y + "px";
	rect.style.left = this.topLeftCorner.x + "px";
	rect.style.border = stroke.style + " " + "#"+stroke.color + " " + stroke.width + "px";
	rect.style.width = this.width + "px";
	rect.style.height = this.height + "px";
	rect.style.background = fill;
	
	document.body.appendChild(rect);
	
	make_NoGray(rect);
	
	this.Obj = rect;
}