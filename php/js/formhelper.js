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

// Formhelper contains functions used in forms, currently, it contains functions
// to automatically create an extra field whenever a certain field is changed.
// The current autocomplete code contains a similar function, but only for
// autocomplete fields. Eventually that code must be integrated with this code.
var formhelper=function() {
    function init() {
        var multi = document.getElementsByClassName("formhelper-multiple");
        for (var i = 0; i < multi.length; i++) {
            for (var c = 0; c < multi[i].childNodes.length; c++) {
                addOnChange(multi[i].childNodes[c]);
            }
        }
    }
    function addOnChange(el) {
        if(el.tagName=="FIELDSET") {
            for (var c = 0; c < el.childNodes.length; c++) {
                el.childNodes[c].addEventListener("change", formhelper.addParentField);
                el.childNodes[c].addEventListener("keyup", formhelper.addParentField);
            }
        } else {
            el.addEventListener("change", formhelper.addCurrentField);
            el.addEventListener("keyup", formhelper.addCurrentField);
        }
    }

    function addParentField() {
        addField(this.parentNode);
    }
    
    function addCurrentField() {
        addField(this);
    } 

    function addField(el) {
        var last = el.parentNode.lastElementChild;
        var fieldset=false;
        if(last.tagName=="FIELDSET") {
            last = last.lastElementChild;
            fieldset=true;
        }
        if(last.value!="") {
            var remove = document.createElement("img");
            remove.addEventListener("click", removeField);
            remove.setAttribute("src", "templates/default/images/icons/remove.png");
            remove.className="actionlink icon";
            el.parentNode.insertBefore(remove, null);

    
            var newfield=el.parentNode.firstElementChild.cloneNode(true);
            addOnChange(newfield);
            if(fieldset) {
                for (var c = 0; c < newfield.childNodes.length; c++) {
                    newfield.childNodes[c].value="";
                }
            }

            newfield.value="";
            el.parentNode.appendChild(newfield);
        }

    }
    
    function removeField() {
        this.parentNode.removeChild(this.previousElementSibling);
        this.parentNode.removeChild(this);
    }

    return {
        init:init,
        removeField:removeField,
        addParentField:addParentField,
        addCurrentField:addCurrentField
    };
}();

window.addEventListener("load", formhelper.init, false);
