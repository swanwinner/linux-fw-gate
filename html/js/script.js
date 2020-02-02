

function wopen3(url, name, w, h, scrollbars, resizable) {
  // Fixes dual-screen position                         Most browsers      Firefox
  var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
  var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
  var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
  var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
  var left = ((width / 2) - (w / 2)) + dualScreenLeft;
  var top = ((height / 2) - (h / 2)) + dualScreenTop;
  var option = "width="+w
          +",height="+h
          +",top="+top
          +",left="+left
          +",scrollbars="+scrollbars
          +",resizable="+resizable;
  open(url, name, option);
}


function spanmark(span) {
  try { span.style.backgroundColor = '#80ff00'; } catch(e){}
  try {
    $(span).find("p").css("background-color", "#80ff00")
  } catch(e) {}
}

function urlGo(url) {
  document.location = url;
}

