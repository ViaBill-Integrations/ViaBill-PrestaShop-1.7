/**
 * ViaBill Custom Code Admin Interface JavaScript
 * Place this file in: viabill/views/js/admin/custom-code.js
 */

$(document).ready(function() {
    
    // Add line numbers to code editors (optional enhancement)
    function addLineNumbers() {
        $('.viabill-code-editor').each(function() {
            var $textarea = $(this);
            
            // Add a wrapper if not already wrapped
            if (!$textarea.parent().hasClass('code-editor-wrapper')) {
                $textarea.wrap('<div class="code-editor-wrapper"></div>');
            }
        });
    }
    
    // Validate code before submission
    function validateCode() {
        var $form = $('form[name="form"]');
        
        if (!$form.length) {
            return;
        }
        
        $form.on('submit', function(e) {
            var cssCode = $('textarea[name="VIABILL_CUSTOM_CSS"]').val();
            var jsCode = $('textarea[name="VIABILL_CUSTOM_JS"]').val();
            
            // Check for potentially dangerous patterns in JavaScript
            var dangerousPatterns = [
                /<script/i,
                /<\/script/i,
                /document\.write/i,
                /eval\(/i
            ];
            
            var warnings = [];
            
            // Check CSS for script tags
            if (cssCode && /<script/i.test(cssCode)) {
                warnings.push('CSS code contains <script> tags. These will be removed automatically.');
            }
            
            // Check JavaScript for dangerous patterns
            dangerousPatterns.forEach(function(pattern) {
                if (jsCode && pattern.test(jsCode)) {
                    if (pattern.source.includes('script')) {
                        warnings.push('JavaScript code contains <script> tags. Please remove them - they are not needed.');
                    } else if (pattern.source.includes('eval')) {
                        warnings.push('JavaScript code contains eval(). This can be dangerous and is not recommended.');
                    } else if (pattern.source.includes('document.write')) {
                        warnings.push('JavaScript code contains document.write(). This may cause issues with modern browsers.');
                    }
                }
            });
            
            // Show warnings if any
            if (warnings.length > 0) {
                var confirmMessage = 'Warning:\n\n' + warnings.join('\n\n') + '\n\nDo you want to continue?';
                
                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }
    
    // Add tab support to textareas
    function enableTabSupport() {
        $('.viabill-code-editor').on('keydown', function(e) {
            // Tab key
            if (e.keyCode === 9) {
                e.preventDefault();
                
                var start = this.selectionStart;
                var end = this.selectionEnd;
                var value = $(this).val();
                
                // Insert tab character
                $(this).val(value.substring(0, start) + '    ' + value.substring(end));
                
                // Move cursor
                this.selectionStart = this.selectionEnd = start + 4;
            }
        });
    }
    
    // Add copy button to example code blocks
    function addCopyButtons() {
        $('.viabill-code-examples pre').each(function() {
            var $pre = $(this);
            var $copyBtn = $('<button class="btn btn-sm btn-default copy-code-btn" style="position: absolute; top: 5px; right: 5px;">Copy</button>');
            
            // Wrap pre in relative container
            if (!$pre.parent().hasClass('code-example-container')) {
                $pre.wrap('<div class="code-example-container" style="position: relative;"></div>');
            }
            
            $pre.parent().append($copyBtn);
            
            $copyBtn.on('click', function(e) {
                e.preventDefault();
                
                var code = $pre.text();
                
                // Create temporary textarea
                var $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(code).select();
                document.execCommand('copy');
                $temp.remove();
                
                // Show feedback
                $copyBtn.text('Copied!');
                setTimeout(function() {
                    $copyBtn.text('Copy');
                }, 2000);
            });
        });
    }
    
    // Character counter
    function addCharacterCounter() {
        $('.viabill-code-editor').each(function() {
            var $textarea = $(this);
            var $counter = $('<div class="character-counter" style="text-align: right; color: #999; font-size: 12px; margin-top: 5px;"></div>');
            
            $textarea.after($counter);
            
            function updateCounter() {
                var length = $textarea.val().length;
                $counter.text(length + ' characters');
            }
            
            $textarea.on('input', updateCounter);
            updateCounter();
        });
    }
    
    // Initialize all features
    addLineNumbers();
    validateCode();
    enableTabSupport();
    addCopyButtons();
    addCharacterCounter();
    
    // Add helpful tooltips
    if (typeof $.fn.tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
    }
});