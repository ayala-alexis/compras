var file = {
    name: function (src) {
        if (src !== '') {
            var nombre = src.split("\\");
            if (nombre.length > 0) {
                return nombre[nombre.length - 1];
            }
        }
        return "";
    },
    type: function (src) {
        var ext = this.name(src);
        if(ext!==''){
            ext = ext.split(".");
            if(ext.length>0){
                return ext[ext.length-1].toLowerCase();
            }
        }
        return "";
    },
    validar: function (src) {
        var ext = this.type(src);
        var ok = false;
        switch (ext) {
            case 'jpeg':
            case 'jpg':
            case 'png':
            case 'gif':
            case 'xlsx':
            case 'zip':
            case 'rar':
			case 'xls':
            case 'docx':
            case 'pdf':
                ok = true;
                break;
            default:
                break;
        }
        return ok;
    }
};