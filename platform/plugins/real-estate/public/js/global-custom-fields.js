(()=>{function t(o){return t="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},t(o)}function o(t,o){for(var e=0;e<o.length;e++){var i=o[e];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(t,n(i.key),i)}}function e(t,o,e){return(o=n(o))in t?Object.defineProperty(t,o,{value:e,enumerable:!0,configurable:!0,writable:!0}):t[o]=e,t}function n(o){var e=function(o,e){if("object"!=t(o)||!o)return o;var n=o[Symbol.toPrimitive];if(void 0!==n){var i=n.call(o,e||"default");if("object"!=t(i))return i;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===e?String:Number)(o)}(o,"string");return"symbol"==t(e)?e:e+""}$(document).ready((function(){(new(function(){return t=function t(){!function(t,o){if(!(t instanceof o))throw new TypeError("Cannot call a class as a function")}(this,t),e(this,"$customFieldOptions",$("#custom-field-options")),e(this,"$customFieldsBox",$("#custom_fields_box"))},(n=[{key:"init",value:function(){this.sortable(),this.handleType(),this.addNewRow(),this.removeRow()}},{key:"handleType",value:function(){var t=this.$customFieldsBox,o=$(".custom-field-type");"dropdown"===o.val()?this.$customFieldsBox.show():this.$customFieldsBox.hide(),o.change((function(){"dropdown"!==$(this).val()?t.hide():t.show()}))}},{key:"sortable",value:function(){$(".option-sortable").sortable({stop:function(){$(".option-sortable").sortable("toArray",{attribute:"data-index"}).map((function(t,o){$('.option-row[data-index="'.concat(t,'"]')).find(".option-order").val(o)}))}})}},{key:"addNewRow",value:function(){this.$customFieldsBox.on("click","#add-new-row",(function(t){var o=$(this).closest(".card").find("table tbody"),e=o.find("tr").last().clone(),n="options[".concat(o.find("tr").length,"][label]"),i="options[".concat(o.find("tr").length,"][value]");e.find(".option-label").val("").attr("name",n),e.find(".option-value").val("").attr("name",i),o.append(e)}))}},{key:"removeRow",value:function(){this.$customFieldOptions.on("click",".remove-row",(function(){var t=$(this).parent().parent(),o=t.parent().find("tr");if(o.length<=1)return o.find(".option-label").val(""),void o.find(".option-value").val("");t.remove()}))}}])&&o(t.prototype,n),i&&o(t,i),Object.defineProperty(t,"prototype",{writable:!1}),t;var t,n,i}())).init()}))})();