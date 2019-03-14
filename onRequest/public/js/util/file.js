function ajaxUploadFile(config)
{
    let testConfig = {
        inputObj: document.getElementById("avatar"),
        inputName:'avatar',
        data :{},
        url:'',
        uploadingProgressFunc: function(percentComplete) {},
        handling:function(result){},
        success:function(result){},
        error:function(response){},
    };
    let fd = new FormData();
    let files = config.inputObj.files;
    let L = files.length;
    let file = {};
    for(let i=0;i<L;i++)
    {
        file = files[i];
        fd.append(config.inputName, file);
    }
    // These extra params aren't necessary but show that you can include other data.
    let field = "";
    for(field in config.data )
    {
        fd.append(field, config.data[field]);
    }
    //
    let xhr = new XMLHttpRequest();
    xhr.open('POST',  config.url, true);//handle_file_upload.php
    xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');

    let isNeedShowProgress = typeof config.uploadingProgressFunc === "function";
    if( true === isNeedShowProgress)
    {
        let percentComplete = 0;
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable)
            {
                percentComplete = (e.loaded / e.total) * 100;
                config.uploadingProgressFunc(percentComplete);
            }
        };
    }

    let response = '';
    xhr.onload = function() {
        response = this.responseText;
        if (this.status === 200)
        {
            console.log('200-response=='+response);
            config.handling(response);
            if( this.readyState === 4)
            {
                console.log('readyState=4-response=='+response);
                let apiData = null;
                try{
                    apiData = JSON.parse(response);
                }
                catch (e)
                {
                    config.error(response);
                }
                if(apiData !== null )
                {
                    config.success(apiData);
                }
            }
        }
    };
    xhr.send(fd);
}
/* let resArr=response.split(';');
let res_mes = resArr[0];
mes.innerHTML=res_mes;
let allSrcs=resArr[1];
let allSrcsArr=allSrcs.split(',');
let L=allSrcsArr.length;
for(let i= 0;i<L;i++)
{
    let img="<img src="+allSrcsArr[i]+" width='20%',height='20%'/>";
    $('p#upload_percent_mes').append(img);
}*/
