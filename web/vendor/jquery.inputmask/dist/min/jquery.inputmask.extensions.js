(function(c){c.extend(c.inputmask.defaults.definitions,{A:{validator:"[A-Za-z]",cardinality:1,casing:"upper"},"#":{validator:"[A-Za-z\u0410-\u044f\u0401\u04510-9]",cardinality:1,casing:"upper"}});c.extend(c.inputmask.defaults.aliases,{url:{mask:"ir",placeholder:"",separator:"",defaultPrefix:"http://",regex:{urlpre1:/[fh]/,urlpre2:/(ft|ht)/,urlpre3:/(ftp|htt)/,urlpre4:/(ftp:|http|ftps)/,urlpre5:/(ftp:\/|ftps:|http:|https)/,urlpre6:/(ftp:\/\/|ftps:\/|http:\/|https:)/,urlpre7:/(ftp:\/\/|ftps:\/\/|http:\/\/|https:\/)/,
urlpre8:/(ftp:\/\/|ftps:\/\/|http:\/\/|https:\/\/)/},definitions:{i:{validator:function(a,b,k,h,f){return!0},cardinality:8,prevalidator:function(){for(var a=[],b=0;8>b;b++)a[b]=function(){var a=b;return{validator:function(b,f,e,d,c){if(c.regex["urlpre"+(a+1)]){var g=b;0<a+1-b.length&&(g=f.buffer.join("").substring(0,a+1-b.length)+""+g);b=c.regex["urlpre"+(a+1)].test(g);if(!d&&!b){e-=a;for(d=0;d<c.defaultPrefix.length;d++)f.buffer[e]=c.defaultPrefix[d],e++;for(d=0;d<g.length-1;d++)f.buffer[e]=g[d],
e++;return{pos:e}}return b}return!1},cardinality:a}}();return a}()},r:{validator:".",cardinality:50}},insertMode:!1,autoUnmask:!1},ip:{mask:"i[i[i]].i[i[i]].i[i[i]].i[i[i]]",definitions:{i:{validator:function(a,b,c,h,f){-1<c-1&&"."!=b.buffer[c-1]?(a=b.buffer[c-1]+a,a=-1<c-2&&"."!=b.buffer[c-2]?b.buffer[c-2]+a:"0"+a):a="00"+a;return/25[0-5]|2[0-4][0-9]|[01][0-9][0-9]/.test(a)},cardinality:1}}},email:{mask:"*{1,20}[.*{1,20}][.*{1,20}][.*{1,20}]@*{1,20}.*{2,6}[.*{1,2}]",greedy:!1,onBeforePaste:function(a,
b){a=a.toLowerCase();return a.replace("mailto:","")},definitions:{"*":{validator:"[A-Za-z\u0410-\u044f\u0401\u04510-9]",cardinality:1,casing:"lower"}}}})})(jQuery);
