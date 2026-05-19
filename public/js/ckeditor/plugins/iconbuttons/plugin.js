// File: public/ckeditor/plugins/iconbuttons/plugin.js

CKEDITOR.plugins.add('iconbuttons', {
    icons: 'checkmark,yellowarrow',
    init: function(editor) {
        
        // Command cho Check Mark
        editor.addCommand('addCheckMark', {
            exec: function(editor) {
                toggleIconWrapper(editor, '/images/success-traces.svg', 'checkmark');
            }
        });
        
        // Command cho Yellow Arrow
        editor.addCommand('addYellowArrow', {
            exec: function(editor) {
                toggleIconWrapper(editor, '/images/warning-arrow.svg', 'yellowarrow');
            }
        });
        
        // Button Check Mark
        editor.ui.addButton('CheckMark', {
            label: 'Thêm Check Mark',
            command: 'addCheckMark',
            toolbar: 'insert',
            icon: this.path + 'icons/checkmark.png'
        });
        
        // Button Yellow Arrow
        editor.ui.addButton('YellowArrow', {
            label: 'Thêm Yellow Arrow',
            command: 'addYellowArrow',
            toolbar: 'insert',
            icon: this.path + 'icons/arrow.png'
        });
        
        // Hàm toggle icon wrapper
        function toggleIconWrapper(editor, iconSrc, iconType) {
            var selection = editor.getSelection();
            var element = selection.getStartElement();
            
            if (!element) return;
            
            // Tìm wrapper icon gần nhất
            var wrapper = findIconWrapper(element);
            
            if (wrapper) {
                var currentIcon = wrapper.findOne('img');
                if (currentIcon) {
                    // Lấy src hiện tại (ưu tiên lấy từ data-cke-saved-src để so sánh chính xác)
                    var currentSrc = currentIcon.getAttribute('data-cke-saved-src') || currentIcon.getAttribute('src');
                    
                    // So sánh: Nếu cùng icon path -> Xóa wrapper
                    // Dùng indexOf để tránh lỗi khi một cái là path tuyệt đối, một cái là tương đối
                    if (currentSrc && currentSrc.indexOf(iconSrc) !== -1) {
                        removeIconWrapper(wrapper, editor);
                    } else {
                        // Khác icon -> Cập nhật cả src và data-cke-saved-src
                        currentIcon.setAttributes({
                            'src': iconSrc,
                            'data-cke-saved-src': iconSrc,
                            'data-icon-type': iconType
                        });
                        // Thông báo cho editor rằng nội dung đã thay đổi
                        editor.fire('change');
                    }
                }
            } else {
                // Chưa có wrapper -> Thêm mới
                var ranges = selection.getRanges();
                if (ranges.length > 0) {
                    addIconWrapper(editor, ranges[0], iconSrc, iconType);
                }
            }
        }
        
        // Tìm wrapper icon bằng cách duyệt ngược lên tree DOM
        function findIconWrapper(element) {
            var current = element;
            while (current && current.type === CKEDITOR.NODE_ELEMENT) {
                if (current.hasAttribute('data-icon-wrapper')) {
                    return current;
                }
                current = current.getParent();
            }
            return null;
        }
        
        // Thêm icon wrapper mới
        function addIconWrapper(editor, range, iconSrc, iconType) {
            var selectedHtml = range.extractContents();
            var fragment = new CKEDITOR.dom.documentFragment(editor.document);
            
            var children = selectedHtml.getChildren();
            var textLines = [];
            
            for (var i = 0; i < children.count(); i++) {
                var child = children.getItem(i);
                if (child.type === CKEDITOR.NODE_TEXT) {
                    var lines = child.getText().split('\n');
                    for (var j = 0; j < lines.length; j++) {
                        if (lines[j].trim()) textLines.push(lines[j].trim());
                    }
                } else if (child.type === CKEDITOR.NODE_ELEMENT) {
                    if (child.getName() !== 'br') {
                        var text = child.getText().trim();
                        if (text) textLines.push(text);
                    }
                }
            }
            
            if (textLines.length === 0) {
                var fallbackText = selectedHtml.getText().trim();
                if (fallbackText) textLines.push(fallbackText);
            }
            
            // Nếu vẫn trống (user không bôi đen text), không làm gì cả
            if (textLines.length === 0) return;

            for (var k = 0; k < textLines.length; k++) {
                var wrapperDiv = createIconWrapper(iconSrc, iconType, textLines[k]);
                fragment.append(wrapperDiv);
                if (k < textLines.length - 1) {
                    fragment.append(new CKEDITOR.dom.element('br'));
                }
            }
            
            range.insertNode(fragment);
            editor.fire('change');
        }
        
        // Hàm tạo cấu trúc HTML cho wrapper
        function createIconWrapper(iconSrc, iconType, textContent) {
            var wrapperDiv = new CKEDITOR.dom.element('div');
            wrapperDiv.setStyles({
                'display': 'flex',
                'align-items': 'center',
                'margin-bottom': '6px'
            });
            wrapperDiv.setAttribute('data-icon-wrapper', 'true');
            
            var iconDiv = new CKEDITOR.dom.element('div');
            iconDiv.setStyles({ 'width': '20px', 'flex-shrink': '0' });
            
            var img = new CKEDITOR.dom.element('img');
            img.setAttributes({
                'src': iconSrc,
                'data-cke-saved-src': iconSrc, // Bắt buộc phải có để CKEditor không ghi đè lại src cũ
                'alt': '',
                'data-icon-type': iconType
            });
            img.setStyles({ 'width': '15px', 'height': '15px' });
            iconDiv.append(img);
            
            var textDiv = new CKEDITOR.dom.element('div');
            textDiv.setStyle('margin-left', '8px');
            textDiv.setText(textContent);
            
            wrapperDiv.append(iconDiv);
            wrapperDiv.append(textDiv);
            
            return wrapperDiv;
        }
        
        // Xóa wrapper và giữ lại text
        function removeIconWrapper(wrapper, editor) {
            // Tìm div chứa text (div thứ 2 bên trong wrapper)
            var textDiv = wrapper.getLast(); 
            if (textDiv && textDiv.type === CKEDITOR.NODE_ELEMENT) {
                // Di chuyển tất cả nội dung bên trong textDiv ra trước wrapper
                textDiv.moveChildren(wrapper.getAscendant('body') || wrapper.getParent(), true);
                textDiv.insertBefore(wrapper);
            }
            wrapper.remove();
            editor.fire('change');
        }
    }
});