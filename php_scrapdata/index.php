<?php
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Seoul');//Asia/Hong_Kong        Asia/Seoul

//-------------FUN
//add
function addCsvFile($lineData, $topHeadLine="TYPE,TEXT,TIME\n", $savePath= __DIR__ .'/tempCSV.csv') { 
    if(!file_exists($savePath)) file_put_contents($savePath, $topHeadLine);
    $time = date('Y-m-d H:i:s'); 
    array_push($lineData, $time);
    $fp = fopen($savePath, 'a+');
    fputcsv($fp, $lineData);
    fclose($fp);
    return true;
} 
//get
function csv2array($filename= __DIR__ .'/tempCSV.csv', $delimiter=','){
    if(!file_exists($filename) || !is_readable($filename)) return FALSE;
    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            if(!$header) $header = $row;
            else $data[] = array_combine($header, $row);
        }
        fclose($handle);
    }
    return $data;
}
//del
function removeCsvFile($filename= __DIR__ .'/tempCSV.csv'){
    if(file_exists($filename)){
        if (!unlink($filename)) return 'DEL_ERROR';
        else return 'DEL_OK';
    }else return 'NO_FILE';
}



//-------------GET
if($_REQUEST['mode'] == 'get'){ //보기
    $data = csv2array();
    if($data == false) echo 'EMPTY';
    else echo json_encode($data, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT);
    exit();
}
else if($_REQUEST['mode'] == 'save'){ //저장
    if(!empty($_REQUEST['text'])){
        if(empty($_REQUEST['type'])) $type = 'TEST';
        else $type = $_REQUEST['type'];
        $check = addCsvFile(array($type, $_REQUEST['text']));
        if($check) return 'OK';
        else echo 'ERROR';
    }else echo 'NO_DATA';
    exit();
}
else if($_REQUEST['mode'] == 'del'){//삭제
    echo removeCsvFile(); 
    exit();
}


//-----------------

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ScrapTempData</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <!-- <script src="nosoruce.js"></script> -->
    <style>
    .line{border:1px solid #ccc; padding:10px; margin-bottom: 10px;}
    .line .head{padding:5px; font-weight: bold;}
    .line .body{padding: 10px 0; border-top:1px solid #eee;}
    </style>
    
</head>
<body>
    
    <div>
        <textarea name="text" id="textInput" placeholder="TEXT" style="width:100%; height:200px;"></textarea>
        <hr>
        <button onclick="saveFun()" title="save data">save</button> <button onclick="removeFun()" title="remove all datas">remove</button> <span id="stateBox"></span>
    </div>
    <hr>
    <div id="resultBox"><pre></pre></div>
    <script>

        $(function(){
            getFun();
        })


        function getFun(){
            $.ajax({
                type: "post",
                url: '?mode=get',
                data: '',
                dataType: "json",
                success: function (res) {
                    console.log(res)
                    // $('#resultBox pre').html(res);
                    let result  = '';
                    res.forEach(function(item, i){
                        result += '<div class="line"><div class="head">NO: '+ (i+1) +' | TYPE:'+ item.TYPE +' | DATE: '+ item.TIME +'</div><div class="body"><pre>'+ item.TEXT +'</pre></div></div>';
                    });
                    $('#resultBox').html(result);
                }
            }); 
        }


        function saveFun(){
            $.ajax({
                type: "post",
                url: "?mode=save",
                data: {
                    'text': $('#textInput').val()
                },
                dataType: "text",
                success: function (res) {
                    $('#stateBox').html(res);
                    getFun();
                }
            });
        }



        function removeFun(){
            $.ajax({
                type: "post",
                url: "?mode=del",
                data: '',
                dataType: "text",
                success: function (res) {
                    $('#stateBox').html(res);
                    getFun();
                }
            });
        }


    //input tab key
    $(document).delegate('#textInput', 'keydown', function(e) {
    var keyCode = e.keyCode || e.which;

    if (keyCode == 9) {
        e.preventDefault();
        var start = this.selectionStart;
        var end = this.selectionEnd;

        // set textarea value to: text before caret + tab + text after caret
        $(this).val($(this).val().substring(0, start)
                    + "\t"
                    + $(this).val().substring(end));

        // put caret at right position again
        this.selectionStart =
        this.selectionEnd = start + 1;
    }
    });

    // setInterval(() => {
    //     console.log(new Date())
    // }, 1000);

    </script>



</body>
</html>



