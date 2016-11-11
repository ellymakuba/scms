/* Slideshow.js
   Author Des Kerrigan
   Date   26/1/2002
*/

var bSlide=0;
var num =0;
var imgName="imgSlide";
var imgHidden="imgHidden";
var bStart=0;

function slides()
{
  if(navigator.appName!="Microsoft Internet Explorer")
  {
    for(var i=1;i<7;i++)
    {
      document.getElementById("td"+i).innerHTML = "<a href='#'>" + document.getElementById("td"+i).innerHTML + "</a>";
    }
  } 
  next_slide();
}
function next_slide()
{
  bSlide = false;
  num++;
  if (num>numpics)
  { 
    num = 1;
  }
  var imgNew = new Image();
  imgNew.src = directory+picsrc[num+1]; //preload next image
  print_new_slide();
}
function previous_slide()
{
  bSlide = false;
  num--;
  if (num<1)
  { 
    num = numpics ;
  }
  var imgNew = new Image();
  imgNew.src = directory+picsrc[num-1]; //preload next image
  print_new_slide();
}
function print_new_slide()
{
  var fwdImage = new Image;
  document['imgHidden'].src=directory+picsrc[num];
  if(bStart>0)
  {
      fadeTrans(imgName, imgHidden, 1200);
      if (num < numpics-1)
        fwdImage.src = directory+picsrc[num+2];
  }
  else
  {
    bStart=1;
    document[imgName].src=document['imgHidden'].src;
  }
  document.form1.text1.value="Slide " + num + " of " + numpics + ": " + txtPic[num];
}
function start_slideshow()
{
  if(bSlide==false)
  {
    bSlide = true;
    slideshow();
  }
}
function slideshow()
{
  if (bSlide==true)
  {
    num++;
    if (num>numpics)
    {
      num=1;
    }
    print_new_slide();
    setTimeout('slideshow()',8000);
    var imgNew = new Image();
    imgNew.src = directory+picsrc[num+1]; //preload next image
  }
}
function large_pictures()
{
  directory = directory_medium;
  print_new_slide();
}
function small_pictures()
{
  directory = directory_small;
  print_new_slide();
}
