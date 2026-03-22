var json_ajax = null;
function request_json(form) {
    var obj_json = undefined;
    try{
        $.ajax({
            url: form.action,
            type: form.method,
            data: new FormData(form),
            cache: false,
            contentType: false,
            processData: false,
            success: function (result) {
                obj_json = JSON.parse(result);
            },
            error: function (xhr, status, error) {
                obj_json = {
                    exito: false,
                    msj: 'Error Aplicacion'
                };
            },
            async:false
        });
    }catch(e){
        obj_json = {
            exito: false,
            msj: 'Error de aplicación'
        };
    }
    return obj_json;
}

function request_json_usuario(form) {
    var obj_json = undefined;
    try{
        var data = new FormData();
        data.append('id_usuario', form.id_usuario);
        data.append('usuario', form.usuario);
        data.append('nombre', form.nombre);
        data.append('correo', form.correo);
        data.append('rol', form.rol);
        
        $.ajax({
            url: form.action,
            type: form.method,
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (result) {
                obj_json = JSON.parse(result);
            },
            error: function (xhr, status, error) {
                obj_json = {
                    exito: false,
                    msj: 'Error Apliación'
                };
            },
            async:false
        });
    }catch(e){
        obj_json = {
            exito: false,
            msj: 'Error de aplicación'
        };
    }
    return obj_json;
}
function request_json_usuario_add(form) {
    var obj_json = undefined;
    try{
        var data = new FormData();
        data.append('password', form.password);
        data.append('usuario', form.usuario);
        data.append('nombre', form.nombre);
        data.append('correo', form.correo);
        data.append('rol', form.rol);
        
        $.ajax({
            url: form.action,
            type: form.method,
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (result) {
                obj_json = JSON.parse(result);
            },
            error: function (xhr, status, error) {
                obj_json = {
                    exito: false,
                    msj: 'Error Apliación'
                };
            },
            async:false
        });
    }catch(e){
        obj_json = {
            exito: false,
            msj: 'Error de aplicación'
        };
    }
    return obj_json;
}
function request_json_id(form) {
    var obj_json = undefined;
    try{
        var data = new FormData();
        data.append('id', form.id);
        $.ajax({
            url: form.action,
            type: form.method,
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (result) {
                obj_json = JSON.parse(result);
            },
            error: function (xhr, status, error) {
                obj_json = {
                    exito: false,
                    msj: 'Error Apliación'
                };
            },
            async:false
        });
    }catch(e){
        obj_json = {
            exito: false,
            msj: 'Error de aplicación'
        };
    }
    return obj_json;
}
function request_json_usuario_pwd(form) {
    var obj_json = undefined;
    try{
        var data = new FormData();
        data.append('id_usuario', form.id_usuario);
        data.append('contra', form.contra);
        $.ajax({
            url: form.action,
            type: form.method,
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (result) {
                obj_json = JSON.parse(result);
            },
            error: function (xhr, status, error) {
                obj_json = {
                    exito: false,
                    msj: 'Error Apliación'
                };
            },
            async:false
        });
    }catch(e){
        obj_json = {
            exito: false,
            msj: 'Error de aplicación'
        };
    }
    return obj_json;
}
function request_json_email(form) {
    var obj_json = undefined;
    try{
        var data = new FormData();
        data.append('id', form.id);
        /*data.append('state', form.state);
        data.append('status', form.status);
        if(form.id_empresa!==undefined){
            data.append('id_empresa', form.id_empresa);
        }
        if(form.id_categoria!==undefined){
            data.append('id_categoria', form.id_categoria);
        }
        if(form.id_user!==undefined){
            data.append('id_user', form.id_user);
        }
        if(form.is5k!==undefined){
            data.append('is5k', form.is5k);
        }
        if(form.dejecutivo!==undefined){
            data.append('dejecutivo', form.dejecutivo);
        }*/
        $.ajax({
            url: 'http://192.168.40.4/report/email.ashx',
            type: 'POST',
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (result) {
                obj_json = JSON.parse(result);
            },
            error: function (xhr, status, error) {
                obj_json = {
                    exito: false,
                    msj: 'Error Aplicación'
                };
            },
            async:true
        });
    }catch(e){
        obj_json = {
            exito: false,
            msj: 'Error de aplicación'
        };
    }
    return obj_json;
}
function request_json_empresa(form) {
    var obj_json = undefined;
    try{
        var data = new FormData();
        data.append('id_empresa', form.id_empresa);
        data.append('id_usuario', form.id_usuario);
        $.ajax({
            url: form.action,
            type: form.method,
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (result) {
                obj_json = JSON.parse(result);
            },
            error: function (xhr, status, error) {
                obj_json = {
                    exito: false,
                    msj: 'Error Apliación'
                };
            },
            async:false
        });
    }catch(e){
        obj_json = {
            exito: false,
            msj: 'Error de aplicación'
        };
    }
    return obj_json;
}