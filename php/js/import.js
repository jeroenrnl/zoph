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


var zImport=function() {
    function startUpload(form, id, num) {
        form.style.display="none";

        updateProgressbar(id + "_" + num);
        
        div=document.getElementById("prog_" + id + "_" + num);
        div.style.display="block";

        num=parseInt(num, 10) + 1;
        createUploadIframe(frameElement, id, num);
        
    }

    function createUploadIframe(frame, id, num) {
        iframe=document.createElement("iframe");
        iframe.src="import.php?_action=browse&num=" + num + "&upload_id=" + id;
        iframe.className="upload";
        iframe.id="upload_" + num;
        iframe.setAttribute("frameBorder", 0);
        iframe.setAttribute("allowTransparency", 1);
        frame.parentNode.insertBefore(iframe, frame);
    }

    function deleteIframe(frame_id) {
        frame=top.document.getElementById(frame_id);
        frameparent=frame.parentNode;
        setTimeout('frameparent.removeChild(frame)', 10000);
    }

    function updateProgressbar(id) {
        setTimeout("zImport.updateProgressbar('" + id + "')", 1000);
        progress=XML.getData("import_progress", id);
    }

    function httpResponse(object, xml) {
        root=xml.getElementsByTagName('importprogress');
        importnode=root[0].firstChild;
        id=importnode.firstChild.firstChild.nodeValue;
        currentnode=importnode.childNodes[1];
        if(currentnode.childNodes.length===0) {
            current=0;
            total=0;
            filename="unknown";
        } else {
            current=currentnode.firstChild.nodeValue;
            total=importnode.childNodes[2].firstChild.nodeValue;
            filename=importnode.childNodes[3].firstChild.nodeValue;
        }
        
        
        fn=document.getElementById("fn_" + id);
        fn.innerHTML=filename;

        MB = parseInt(parseInt(total, 10) / 1024 / 102.4, 10) / 10;

        size=document.getElementById("sz_" + id);
        size.innerHTML=MB.toString() + " MiB";

        progressdiv=document.getElementById("pb_" + id + "_inner");
        if(total > 0) {
            percent = parseInt(
                parseInt(current, 10) / parseInt(total,10) * 100,10);
        } else {
            percent=0;
        }
        progressdiv.innerHTML=percent + "%";
        progressdiv.style.width=percent.toString() + "%";
    }

    function getThumbs(notimer) {
        var http=new XMLHttpRequest();
        http.open("GET", "getxmldata.php?object=import_thumbs", true);
        http.onreadystatechange=function() { 
            zImport.showThumbs(http); 
        };
        http.send(null);
        if(!notimer) {
            setTimeout(function() { zImport.getThumbs(false); }, 15000);
        }
    }

    function showThumbs(http) {
        var content;
        var status;
        var submit;

        if (http.readyState == 4) {
            if(http.status == 200) {
                response=http.responseXML;
                thumbswindow=document.getElementById("import_thumbs");
                thumbs=document.getElementById("import_thumbnails");
                
                files=response.getElementsByTagName("file");
                ids=[];
                if(files.length>0) {
                    thumbswindow.style.display="block";
                    for(var i=0; i<files.length; i++) {
                        md5=null;
                        status=null;
                        icon=null;
                        for(var c=0; c<files[i].childNodes.length; c++) {
                            tag=files[i].childNodes[c];
                            content=null;
                            if(tag.textContent) {
                                content=tag.textContent;
                            } else {
                                // And again, M$ is to dumb to follow simple standards
                                content=tag.text;
                            }
                            switch(tag.nodeName) {
                            case "md5": 
                                md5=content;
                                ids.push(md5);
                                break;
                            case "status":
                                status=content;
                                break;
                            case "icon":
                                icon=content;
                                break;
                            }
                        }
                        deleteli=document.createElement("li");
                        actionlinks=document.createElement("ul");
                        
                        actionlinks.className="actionlink";
                        
                        del=createNode("a", translate['delete']);
                        del.href="#";
                        del.setAttribute("onClick", "zImport.doAction('delete', '" + md5 + "'); return false");
                        deleteli.appendChild(del);

                        retryli=document.createElement("li");
                        retry=createNode("a", translate['retry']);
                        retry.href="#";
                        retry.setAttribute("onClick", "zImport.doAction('retry', '" + md5 + "'); return false");
                        retryli.appendChild(retry);
                        
                        actionlinks.appendChild(deleteli);

                        checkbox=document.createElement("input");
                        checkbox.setAttribute("type", "checkbox");
                        checkbox.setAttribute("name", "cb_" + md5);
                        checkbox.className="thumb_checkbox";
                        
                        existing=document.getElementById(md5);
                        if(existing) {
                            for(var e=0; e<existing.childNodes.length; e++) {
                                tag=existing.childNodes[e];
                                if(tag.nodeName=="IMG") {
                                    if(((tag.className=="waiting" || tag.className=="busy") &&
                                          (status=="done" || status=="ignore")) 
                                          || tag.className=="" && status!="done"){
                                        // so this thumb is out of sync with
                                        // the status on disk. Could be due to
                                        // clicking 'back' or 'reload'
                                        // We delete it, so it will be recreated 
                                        // correctly the next round.
                                        deleteNode(tag.parentNode);
                                        setTimeout(function() { zImport.getThumbs(true); }, 500);
                                    }
                                }
                            }

                        } else {
                            div=document.createElement("div");
                            div.id=md5;
                            div.className="thumbnail";
                            name=files[i].getAttribute("name");
                            type=files[i].getAttribute("type");
                            
                            filename=createNode("span", name);
                            filename.className="filename";

                            img=document.createElement("img");
                            switch(status) {
                            case "done":
                                if(type=="xml") {
                                    imgsrc=icon;
                                    importli=document.createElement("li");
                                    xmlimport=createNode("a", translate['import']);
                                    xmlimport.href="#";
                                    xmlimport.setAttribute("onClick", "zImport.doAction('process', '" + md5 + "'); return false");
                                    importli.appendChild(xmlimport);
                                    actionlinks.appendChild(importli);
                                } else {
                                    actionlinks.appendChild(retryli);
                                    div.appendChild(checkbox);
                                    imgsrc="image.php?type=import_thumb" +
                                       "&file=" + md5;
                                    img.setAttribute("onmouseover", "zImport.createPreviewDiv('" + md5 +"');");
                                    img.setAttribute("onmouseout", "zImport.destroyPreviewDiv('" + md5 +"');");
                                }
                                break;
                            case "waiting":
                                img.className="waiting";
                                imgsrc=icon;    
                                break;
                            case "ignore":
                                img.className="ignore";
                                actionlinks.appendChild(retryli);
                                imgsrc=icon;    
                                break;
                            default:
                                imgsrc="";
                                break;
                            }
                            img.setAttribute("src", imgsrc);
                            div.appendChild(actionlinks);
                            div.appendChild(img);
                            div.appendChild(filename);
                            thumbs.appendChild(div);
                        }
                    }
                    
                } else {
                    thumbswindow.style.display="none";
                }
                // Remove all thumbs for which the file no longer
                // exists
                for(var t=0; t<thumbs.childNodes.length; t++) {
                    thumb=thumbs.childNodes[t];
                    if(thumb.className=="thumbnail") {
                        if(findInArray(ids,thumb.id)==-1) {
                            thumbs.removeChild(thumb);
                            setTimeout(function() { zImport.getThumbs(true); }, 500);
                        }
                    }
                }

                // Sort the nodes by Filename
                names=getElementsByClass("filename");
                oldfile="";
                for(var f=0; f<names.length; f++) {
                    file=names[f].innerHTML;
                    if(file<oldfile) {
                        for(var n=0; n<names.length; n++) {
                            if(file<names[n].innerHTML) {
                                   names[f].parentNode.parentNode.insertBefore(names[f].parentNode, names[n].parentNode);
                                   break;
                            }
                        }
                    } else {
                        oldfile=file;
                    }
                }
                // Re-enable the submit button
                submit=document.getElementById("import_submit");
                submit.disabled=false;
                
                processFiles();
                    
            }
        }
    }

    function processFiles() {
        waiting=getElementsByClass("waiting");
        busy=getElementsByClass("busy");
        if(parallel < 1) {
            parallel = 1;
        }
        if(waiting.length > 0 && busy.length < parallel) {
            busy=waiting[0];
            busy.className="busy";
            thumbs=top.document.getElementById("import_thumbs");
            thumbs.style.display="block";
            icon=busy.src;
            iconpath=icon.substr(0,icon.lastIndexOf('/'));
            md5=busy.parentNode.id;
            filename=busy.nextSibling.innerHTML;

            switch(getFileType(filename)) {
            case "image":
                busy.src=iconpath + "/resize.png";
                break;
            case "archive":
                busy.src=iconpath + "/unpack.png";
                break;
            }
            doAction("process", md5);
        } else {
        }
    }

    function processDone(html) {
        if(html) {
            output=top.document.getElementById("import_details_text");
            p=document.createElement("p");
            t=document.createElement("p");
            t.innerHTML=html;
            output.appendChild(p);
            p.innerHTML=t.innerHTML;
            output.parentNode.style.display="block";
        }
    }

    function doAction(action,md5) {
        var http=new XMLHttpRequest();
        http.open("GET", "import.php?_action=" + action + "&file=" + md5, true);
        thumb=document.getElementById(md5);
        if(action=="delete" || action=="retry") {
            deleteNode(thumb);
        }
        http.onreadystatechange=function() {
            XML.httpResponse(http,'action');
        };
        http.send(null);
        setTimeout(function() { zImport.getThumbs(true); }, 500);
    }

    function deleteSelected() {
        var images=document.getElementsByClassName("thumb_checkbox");
        var toDelete=[];
        for(var i=0; i<images.length; i++) {
            if(images[i].checked) {
                var cb=images[i].name.split("_");
                var id=cb[1];
                toDelete.push(id);
             }
         }
        for(var i=0; i<toDelete.length; i++) {
            doAction("delete", toDelete[i]);
         }
    }

    function toggleSelection() {
        var images=document.getElementsByClassName("thumb_checkbox");
        for(var i=0; i<images.length; i++) {
            if(images[i].checked) {
                images[i].checked=false;
            } else {
                images[i].checked=true;
            }
         }
    }

    function selectAll() {
        var images=document.getElementsByClassName("thumb_checkbox");
        for(var i=0; i<images.length; i++) {
            images[i].checked=true;
         }
    }

    function importPhotos() {
        var submit;
        var toImport=0;

        form=document.getElementById("import_form");
        // Disable the submit button to prevent submitting twice
        // it will be reactivated after refreshing the thumbnails

        submit=document.getElementById("import_submit");
        submit.disabled=true;

        // Delete the old checkboxes, if any.
        fieldset=document.getElementById("import_checkboxes");
        if(fieldset) {
            removeChildren(fieldset);
        } else {
            fieldset=document.createElement("fieldset");
            fieldset.id="import_checkboxes";
            form.appendChild(fieldset);
        }
        // Now copy the checkboxes from the form above into this form
        images=document.getElementsByClassName("thumb_checkbox");
        for(var i=0; i<images.length; i++) {
            if(images[i].checked) {
                input=document.createElement("input");
                input.name="_import_image[" + toImport + "]";
                input.className="import_image";

                cb=images[i].name.split("_");
                input.value=cb[1];

                fieldset.appendChild(input);
                toImport++;
            }
        }
        if(toImport>0) {
            XML.submitForm(form, "import.php?_action=import");
        } else {
            alert("You need to select at least one photo");
        }
    }

    function createPreviewDiv(md5) {
        var div=document.createElement("div");
        var img=document.createElement("img");
        var body=document.getElementsByTagName("body")[0];
        
        div.className="preview";
        div.id="preview"+md5;

        img.setAttribute("src", "image.php?type=import_mid" +
            "&file=" + md5);
        div.appendChild(img);

        body.appendChild(div);
    }
    
    function destroyPreviewDiv(md5) {
        div=document.getElementById("preview" + md5);
        deleteNode(div);
    }

    return {
        getThumbs:getThumbs,
        showThumbs:showThumbs,
        startUpload:startUpload,
        deleteSelected:deleteSelected,
        selectAll:selectAll,
        toggleSelection:toggleSelection,
        updateProgressbar:updateProgressbar,
        deleteIframe:deleteIframe,
        doAction:doAction,
        httpResponse:httpResponse,
        processDone:processDone,
        importPhotos:importPhotos,
        createPreviewDiv:createPreviewDiv,
        destroyPreviewDiv:destroyPreviewDiv
    };
}();

if(window == top) {
    if(window.addEventListener) {
        window.addEventListener("load",function(){ zImport.getThumbs(false); },false);
    } else {
        // The clowns at M$ had to invent their own "standard"... again.
        window.attachEvent("onload", function(){ zImport.getThumbs(false); });
    }

}
