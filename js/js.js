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
function request_json_prod(form,ft) {
    var obj_json = undefined;
    try{
        var data = new FormData();
        data.append('id', form.id);
        data.append('buscar', form.buscar);
        $.ajax({
            url: form.action,
            type: form.method,
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (result) {
                obj_json = JSON.parse(result);
                ft(obj_json);
            },
            error: function (xhr, status, error) {
                obj_json = {
                    exito: false,
                    msj: 'Error Apliación'
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
            url: 'http://192.168.40.4/report/email_test.ashx',
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
function request_json_proveedor(form) {
    var obj_json = undefined;
    try{
        var data = new FormData();
        data.append('id_empresa', form.id_empresa);
        data.append('proveedor', form.proveedor);
        $.ajax({
            url: form.action,
            type: form.method,
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (result) {
                obj_json = JSON.parse(result);
                console.log(obj_json);
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
            msj: 'Error de aplicación ' + e.message
        };
    }
    return obj_json;
}
function request_json_categoria_cc(form) {
    var obj_json = undefined;
    try{
        var data = new FormData();
        data.append('id_empresa', form.id_empresa);
        data.append('id_cc', form.id_cc);
        $.ajax({
            url: form.action,
            type: form.method,
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (result) {
                obj_json = JSON.parse(result);
                console.log(obj_json);
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
            msj: 'Error de aplicación ' + e.message
        };
    }
    return obj_json;
}

function request_json_flujo_aprobacion(form) {
    var obj_json = undefined;
    try{
        var data = new FormData();
        data.append('id_empresa', form.id_empresa);
        data.append('id_cc', form.id_cc);
        data.append('id_categoria', form.id_categoria);
        data.append('monto', form.monto);
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
                //console.log(obj_json);
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
            msj: 'Error de aplicación ' + e.message
        };
    }
    return obj_json;
}

function request_json_categoria_add(form,ftn) {
    var obj_json = undefined;
    var data = new FormData();
    data.append('gerencia', form.gerencia);
    data.append('categoria', form.categoria);
    data.append('aprobador1', form.aprobador1);
    data.append('aprobador2', form.aprobador2);
    data.append('aprobador3', form.aprobador3);
    data.append('requiere_5k', form.requiere_5k);
    data.append('aprobador_5k', form.aprobador_5k);
    data.append('requiere_recepcion', form.requiere_recepcion);
    data.append('estado', form.estado);
    try{
        $.ajax({
            url: form.action,
            type: form.method,
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (result) {
                console.log(result);
                obj_json = JSON.parse(result);
                ftn(obj_json);
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
            msj: 'Error de aplicación ' + e.message
        };
    }
    return obj_json;
}

function request_json_categoria_edit(form,ftn) {
    var obj_json = undefined;
    var data = new FormData();
    data.append('id', form.id);
    data.append('gerencia', form.gerencia);
    data.append('categoria', form.categoria);
    data.append('aprobador1', form.aprobador1);
    data.append('aprobador2', form.aprobador2);
    data.append('aprobador3', form.aprobador3);
    data.append('requiere_5k', form.requiere_5k);
    data.append('aprobador_5k', form.aprobador_5k);
    data.append('requiere_recepcion', form.requiere_recepcion);
    data.append('estado', form.estado);
    try{
        $.ajax({
            url: form.action,
            type: form.method,
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (result) {
                console.log(result);
                obj_json = JSON.parse(result);
                ftn(obj_json);
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
            msj: 'Error de aplicación ' + e.message
        };
    }
    return obj_json;
}