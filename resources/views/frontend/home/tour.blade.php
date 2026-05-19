<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đầu tư Hà Nội</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      height: 100vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    header {
      background: #333;
      color: #fff;
      padding: 10px 20px;
      text-align: center;
      font-size: 18px;
    }
    .embed-container {
      flex: 1;
      overflow: hidden;
    }
    .embed-container iframe {
      width: 100%;
      height: 100%;
      border: none;
    }
  </style>
</head>
<body>
  <iframe src="{{ $link_vrtour }}" allowfullscreen style="height:100%;width:100%"></iframe>
</body>
</html>
