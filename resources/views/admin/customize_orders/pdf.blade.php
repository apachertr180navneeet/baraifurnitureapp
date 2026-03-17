<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customize Order</title>

    <style>
        body{
            font-family: DejaVu Sans, sans-serif;
            font-size:14px;
        }

        .container{
            width:100%;
        }

        .title{
            font-size:22px;
            font-weight:bold;
            margin-bottom:20px;
        }

        .info{
            margin-bottom:15px;
        }

        .images{
            margin-top:20px;
        }

        .image-box{
            display:inline-block;
            margin:10px;
        }

        .image-box img{
            width:200px;
            height:auto;
            border:1px solid #ccc;
            padding:5px;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="title">Customize Order</div>

    <div class="info">
        <strong>Customer :</strong> {{ $order->customer->full_name ?? '' }}
    </div>

    <div class="info">
        <strong>Date :</strong> {{ $order->date }}
    </div>

    <div class="info">
        <strong>Remark :</strong> {{ $order->remark }}
    </div>

    <hr>

    <h3>Images</h3>

    <div class="images">

        @if(!empty($images))
            @foreach($images as $img)

                <div class="image-box">
                    <img src="{{ public_path(str_replace(url('/'), '', $img)) }}">
                </div>

            @endforeach
        @else
            <p>No Images</p>
        @endif

    </div>

</div>

</body>
</html>