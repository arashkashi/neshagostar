/* This compressed file is part of Xinha. For uncompressed sources, forum, and bug reports, go to xinha.org */
/* This file is part of version 0.96beta2 released Fri, 20 Mar 2009 11:01:14 +0100 */
function Template(c){this.editor=c;var a=c.config;var b=this;a.registerButton({id:"template",tooltip:Xinha._lc("Insert template","Template"),image:c.imgURL("ed_template.gif","Template"),textMode:false,action:function(d){b.buttonPress(d)}});a.addToolbarElement("template","inserthorizontalrule",1)}Template._pluginInfo={name:"Template",version:"1.0",developer:"Udo Schmal",developer_url:"http://www.schaffrath-neuemedien.de/",c_owner:"Udo Schmal & Schaffrath NeueMedien",license:"htmlArea"};Template.prototype.onGenerate=function(){this.editor.addEditorStylesheet(Xinha.getPluginDir("Template")+"/template.css")};Template.prototype.buttonPress=function(a){a._popupDialog("plugin://Template/template",function(i){if(!i){return false}var c=a._doc.getElementsByTagName("body");var b=c[0];function e(k){var j=a._doc.getElementById(k);if(!j){j=a._doc.createElement("div");j.id=k;j.innerHTML=k;b.appendChild(j)}if(j.style){j.removeAttribute("style")}return j}var g=e("content");var h=e("menu1");var f=e("menu2");var d=e("menu3");switch(i.templ){case"1":h.style.position="absolute";h.style.right="0px";h.style.width="28%";h.style.backgroundColor="#e1ddd9";h.style.padding="2px 20px";g.style.position="absolute";g.style.left="0px";g.style.width="70%";g.style.backgroundColor="#fff";f.style.visibility="hidden";d.style.visibility="hidden";break;case"2":h.style.position="absolute";h.style.left="0px";h.style.width="28%";h.style.height="100%";h.style.backgroundColor="#e1ddd9";g.style.position="absolute";g.style.right="0px";g.style.width="70%";g.style.backgroundColor="#fff";f.style.visibility="hidden";d.style.visibility="hidden";break;case"3":h.style.position="absolute";h.style.left="0px";h.style.width="28%";h.style.backgroundColor="#e1ddd9";f.style.position="absolute";f.style.right="0px";f.style.width="28%";f.style.backgroundColor="#e1ddd9";g.style.position="absolute";g.style.right="30%";g.style.width="60%";g.style.backgroundColor="#fff";d.style.visibility="hidden";break}},null)};