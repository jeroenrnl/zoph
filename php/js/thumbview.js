// This file is part of Zoph.
//
// Zoph is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
// 
// Zoph is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with Zoph; if not, write to the Free Software
// Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

var thumbview = function () {

    function init() {
        var popup = getElementsByClass('popup');

        for (var i=0; i<popup.length; i++) {
            popup[i].onmouseover=thumbview.showDetails;
            popup[i].onmouseout=thumbview.destroyDetails;
        }
    }
    
    function toggle(obj) {
        if(obj.className.indexOf("collapsed")>=0) {
            obj.className=obj.className.replace(/\bcollapsed\b/g,'expanded');
        } else if(obj.className.indexOf("expanded")>=0) {
            obj.className=obj.className.replace(/\bexpanded\b/g,'collapsed');
        }
    }

    function collapseall(id) {
        obj=document.getElementById(id);
        nodes=getElementsByClass('expanded', obj);
        for(var i=0; i<nodes.length; i++) {
            nodes[i].className=nodes[i].className.replace(/\bexpanded\b/g,'collapsed');
        }
    }

    function expandall(id) {
        obj=document.getElementById(id);
        nodes=getElementsByClass('collapsed',obj);
        for(var i=0; i<nodes.length; i++) {
            nodes[i].className=nodes[i].className.replace(/\bcollapsed\b/g,'expanded');
        }
    }

    function showDetails(e) {
        var body=document.getElementsByTagName("body")[0];
        
        var id=this.id.split("_");
        id.shift();
       
        
        var div=document.createElement("div");
        div.className="details";
        div.id="details_" + id.join("_");
        div.style.visibility="hidden";
        
        body.appendChild(div);
        
       
        XML.getData(div.id);
    }

    function httpResponse(xml) {
        
        var request=xml.getElementsByTagName("request")[0];

        var classname=request.getElementsByTagName("class")[0].firstChild.nodeValue;
        var id=request.getElementsByTagName("id")[0].firstChild.nodeValue;
       

        var div=document.getElementById("details_" + classname + "_" + id);
        if(div) {
            removeChildren(div);
            var dl=document.createElement("dl");

            var li_id="thumb_" + classname + "_" + id;

            var li=document.getElementById(li_id);

            var detail=xml.getElementsByTagName("detail");
            var title, dt, dd, icon, subject, data;

            for(var i=0; i<detail.length; i++) {

                subject=detail[i].getElementsByTagName("subject")[0].firstChild.nodeValue;
                data=detail[i].getElementsByTagName("data")[0].firstChild.nodeValue;
            
                if(subject==="title") {
                    title=createNode("h3", data);
                    div.appendChild(title);
                } else {
                    dt=document.createElement("dt");
                    icon=document.createElement("img");

                    icon.setAttribute("src", icons[subject] );
                    icon.setAttribute("alt", subject);
                    dt.appendChild(icon);
                    dd=createNode("dd", data);
                    
                    dl.appendChild(dt);
                    dl.appendChild(dd);
                }
            }
            div.appendChild(dl);
        
            div.style.left=findPos(li)[0] - 20 + "px";
            div.style.top=findPos(li)[1] - div.offsetHeight + "px";
            div.style.visibility="visible";
       } 
   }

   function destroyDetails(e) {
        var id=this.id.split("_");
        id.shift();
        deleteNode(document.getElementById("details_" + id.join("_")));
   }

    return {
        init:init,
        toggle:toggle,
        collapseall:collapseall,
        expandall:expandall,
        showDetails:showDetails,
        destroyDetails:destroyDetails,
        httpResponse:httpResponse
    };

}();


if(window.addEventListener) {
    window.addEventListener("load",thumbview.init,false);
} else {
    // Why can't M$ simply follow the standard?
    window.attachEvent("onload",thumbview.init);
}
