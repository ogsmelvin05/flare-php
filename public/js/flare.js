if (!String.prototype.trim) {
    String.prototype.trim = function (chr) {
        return this.replace((!chr) ? new RegExp('^\\s+|\\s+$', 'g') : new RegExp('^' + chr + '+|' + chr + '+$', 'g'), '');
    }
}

if (!String.prototype.rtrim) {
    String.prototype.rtrim = function (chr) {
        return this.replace((!chr) ? new RegExp('\\s+$') : new RegExp(chr + '+$'), '');
    }
}

if (!String.prototype.ltrim) {
    String.prototype.ltrim = function (chr) {
        return this.replace((!chr) ? new RegExp('^\\s+') : new RegExp('^' + chr + '+'), '');
    }
}

if (!String.prototype.capitalize) {
    String.prototype.capitalize = function() {
        return this.charAt(0).toUpperCase() + this.slice(1);
    }
}

if (!Array.prototype.indexOf) {
    Array.prototype.indexOf=function(o,i){for(var j=this.length,i=i<0?i+j<0?0:i+j:i||0;i<j&&this[i]!==o;i++);return j<=i?-1:i}
}

if (!String.prototype.bin2hex) {
    String.prototype.bin2hex = function () {
        var i, l, o = "", n;
        var s = this.toString();
        for (i = 0, l = s.length; i < l; i++) {
            n = s.charCodeAt(i).toString(16);
            o += n.length < 2 ? "0" + n : n;
        }
        return o;
    }
}

function clone(obj) {
    return new function () {
        this.prototype = obj;
    }
}

var Flare = {};

Flare.Application = {
    create : function () {

    }
}