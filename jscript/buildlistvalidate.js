// JavaScript Document

var rootcomps = new Array();
var childcomps = new Array();

function validateForm()
{
	var f=document.forms["builtlist"];
	if (f==null || f=="")
	{
  	  alert("no form found");
  	  return false;
  	}
  	else
	{
	  alert("check validity"); 	
	}
}

function getForm()
{
	var f=document.forms["builtlist"];
	if (f==null || f=="")
	{
  	  alert("no form found");
  	  return false;
  	}
  	else
	{
	  return f;
	}	
	
}

function onChangeVal(compid)
{
  scroller=document.getElementById('buildrng_'+compid);	
  label=document.getElementById('rangevalue_'+compid);	
  label.value=scroller.value;
}