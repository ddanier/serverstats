function tree(el) {
 var fullyOpen = false;
 if (el.className.indexOf('fully-open') != -1) {
  fullyOpen = true;
 }
 el.className = 'treed';
 var lis = el.getElementsByTagName('li');
 for (var i = 0; i < lis.length; i++) {
  var uls = lis[i].getElementsByTagName('ul');
  if (uls.length > 0) {
   if (lis[i].className == 'open' || fullyOpen) {
    uncollapse(lis[i]);
   } else {
    collapse(lis[i]);
   }
   lis[i].onmousedown = function(e) {
    this.className = (this.className.substring(0, 4) == 'last' ? (this.className == 'lastopen' ? 'lastcollapsed' : 'lastopen') : (this.className == 'open' ? 'collapsed' : 'open'));
    clicker(e);
   }
  } else {
   lis[i].className = 'file';
  }
  if (!nextElement(lis[i])) {
   lis[i].className = 'last' + lis[i].className;
  }
 }
 var as = el.getElementsByTagName('a');
 for (var i = 0; i < as.length; i++) {
  as[i].onmousedown = function(e) {
   clicker(e);
  }
 }
}
function collapse(li) {
 li.className = 'collapsed';
}
function uncollapse(li) {
 li.className = 'open';
}
function nextElement (node) {
 try {
  do {
   node = node.nextSibling;
  } while (node.nodeType != 1);
  return node;
 } catch (e) {
  return false;
 }
}
function clicker(e) {
 if (!e) var e = window.event;
 e.cancelBubble = true;
 if (e.stopPropagation) e.stopPropagation();
}
window.onload = function() {
 if (document.getElementById && document.getElementsByTagName) {
  var uls = document.getElementsByTagName('ul');
  for (var i = 0; i < uls.length; i++) {
   if (uls[i].className.indexOf('tree') != -1) {
    tree(uls[i]);
   }
  }
 }
}