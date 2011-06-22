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

var thumbview=function() {
    function toggle(obj) {
        if(obj.className.indexOf("collapsed")>=0) {
            obj.className=obj.className.replace(/\bcollapsed\b/g,'expanded');
        } else if(obj.className.indexOf("expanded")>=0) {
            obj.className=obj.className.replace(/\bexpanded\b/g,'collapsed');
        }
    }

    function collapseall(id) {
        obj=document.getElementById(id);
        nodes=obj.getElementsByClassName('expanded');
        while(nodes.length>0) {
            nodes[0].className=nodes[0].className.replace(/\bexpanded\b/g,'collapsed');
        }
    }

    function expandall(id) {
        obj=document.getElementById(id);
        nodes=obj.getElementsByClassName('collapsed');
        while(nodes.length>0) {
            nodes[0].className=nodes[0].className.replace(/\bcollapsed\b/g,'expanded');
        }
    }

    return {
        toggle:toggle,
        collapseall:collapseall,
        expandall:expandall
    };
}();
