CKEDITOR.plugins.add("CustomImage", {
  init: function (editor) {
    // Command mở CKFinder chọn ảnh
    editor.addCommand("customImageCommand", {
      exec: function (editor) {
        CKFinder.popup({
          chooseFiles: true,
          onInit: function (finder) {
            finder.on("files:choose", function (evt) {
              var file = evt.data.files.first();
              var imageUrl = file.getUrl();

              // Nếu là icon đặc biệt thì chèn trực tiếp với 15x15
              if (
                imageUrl.includes("success-traces.svg") ||
                imageUrl.includes("warning-arrow.svg")
              ) {
                editor.insertHtml(
                  '<img src="' +
                    imageUrl +
                    '" style="width:15px;height:15px;"/>'
                );
              } else {
                // Các ảnh khác thì mở cropper
                openCropperModal(imageUrl, function (croppedDataUrl) {
                  editor.insertHtml(
                    '<img src="' +
                      croppedDataUrl +
                      '" style="max-width:100%; display:block; margin:auto;"/>'
                  );
                });
              }
            });
          },
        });
      },
    });

    // Nút toolbar
    // editor.ui.addButton("CustomImage", {
    //   label: "Chèn ảnh có crop",
    //   command: "customImageCommand",
    //   toolbar: "insert",
    //   icon: this.path + "icons/customimage.png",
    // });

    // Xử lý khi paste
    editor.on("paste", function (evt) {
      var data = evt.data;
      if (!data || !data.dataValue) return;

      var tempDiv = document.createElement("div");
      tempDiv.innerHTML = data.dataValue;

      var imgs = tempDiv.querySelectorAll("img");
      if (imgs.length > 0) {
        evt.cancel(); // chặn default insert
        let htmlOutput = tempDiv.innerHTML;

        imgs.forEach((img, idx) => {
          let src = img.src;

          if (
            src.includes("success-traces.svg") ||
            src.includes("warning-arrow.svg")
          ) {
            // Chèn trực tiếp với size fix 15x15
            let fixedImg =
              '<img src="' + src + '" style="width:15px;height:15px;"/>';
            htmlOutput = htmlOutput.replace(img.outerHTML, fixedImg);
          } else {
            // Với ảnh khác thì mở cropper
            // let placeholder = `<span id="crop_placeholder_${idx}"></span>`;
            // htmlOutput = htmlOutput.replace(img.outerHTML, placeholder);

            // openCropperModal(src, function (croppedDataUrl) {
            //   let span = document.getElementById(`crop_placeholder_${idx}`);
            //   if (span) {
            //     span.outerHTML =
            //       '<img src="' +
            //       croppedDataUrl +
            //       '" style="max-width:100%; display:block; margin:auto;"/>';
            //   }
            // });
          }
        });

        editor.insertHtml(htmlOutput);
      }

      // Trường hợp paste file ảnh từ clipboard
      if (data && data.dataTransfer && data.dataTransfer.getFilesCount()) {
        var file = data.dataTransfer.getFile(0);
        if (file && file.type.indexOf("image") === 0) {
          evt.cancel();

          var reader = new FileReader();
          reader.onload = function (e) {
            openCropperModal(e.target.result, function (croppedDataUrl) {
              editor.insertHtml(
                '<img src="' +
                  croppedDataUrl +
                  '" style="max-width:100%; display:block; margin:auto;"/>'
              );
            });
          };
          reader.readAsDataURL(file);
        }
      }
    });
  },
});

// === Hàm mở cropper modal ===
function openCropperModal(src, callback) {
  let modal = document.createElement("div");
  modal.style.cssText = `
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center;
    z-index: 99999;
  `;
  modal.innerHTML = `
    <div style="
      background: #fff; padding: 20px; border-radius: 10px; 
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
      width: 90%; max-width: 800px; text-align: center;
    ">
      <div style="margin-bottom:15px;">
        <img id="crop-image" src="${src}" style="
          max-width: 100%; max-height: 500px;
          border: 1px solid #ddd; border-radius: 6px;
        "/>
      </div>
      <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: center;">
        <button id="crop-confirm" style="
          padding: 8px 16px; font-size: 14px;
          border-radius: 6px; cursor: pointer; border: none;
          background: #4caf50; color: #fff;
        ">Cắt & Chèn</button>
        <button id="crop-cancel" style="
          padding: 8px 16px; font-size: 14px;
          border-radius: 6px; cursor: pointer; border: none;
          background: #f44336; color: #fff;
        ">Hủy</button>
      </div>
    </div>
  `;
  document.body.appendChild(modal);

  let image = modal.querySelector("#crop-image");
  let cropper;

  image.onload = function () {
    cropper = new Cropper(image, {
      aspectRatio: 16 / 9,
      viewMode: 1,
      autoCropArea: 1,
    });
  };

  modal.querySelector("#crop-confirm").onclick = function () {
    if (!cropper) return;
    let canvas = cropper.getCroppedCanvas({ width: 800, height: 450 });
    callback(canvas.toDataURL("image/jpeg"));
    document.body.removeChild(modal);
  };

  modal.querySelector("#crop-cancel").onclick = function () {
    document.body.removeChild(modal);
  };
}
