(function($) {
    'use strict';
    $(document).ready(function() {
        $(".mw-pro-popup-overlay").click(function() {
            $(".mw-image-overlay").css({
                "opacity": "1",
                "visibility": "visible"
            })
        });
        $('.mw-image-overlay').click(function() {
            $(".mw-image-overlay").css({
                "opacity": "0",
                "visibility": "hidden"
            })
        });
        //multiple chekbox unchake for pro
        function handleCheckboxClick(event) {
            const checkbox = event.target;
            // Toggle the checkbox state
            checkbox.checked = !checkbox.checked;
        }
        const checkboxes = document.querySelectorAll('.mw-pro-popup-overlay input[type="checkbox"]');
        // Add event listeners to checkboxes
        checkboxes.forEach(checkbox => {
            // Uncheck if already checked
            if (checkbox.checked) {
                checkbox.checked = false;
            }
            // Add click event listener
            checkbox.addEventListener('click', handleCheckboxClick);
        });
        //forced checked checkbox
        const forcecheckbox = document.querySelectorAll('.forceCheckCheckbox');
        var warningShown = false;
        // Add a click event listener to the checkbox
        if(forcecheckbox)
        forcecheckbox.forEach((checkbox, index) => {
            checkbox.addEventListener('click', function () {
                // Once checked, disable the checkbox
                if (!checkbox.checked) {
                    if(!warningShown){
                        const warningHTML = '<div class="mw-warning-massage" id="warningMessage-' + index + '" style="position: relative;">' + MooWoodleAppLocalizer.lang.warning_to_force_checked + '</div>';
                        checkbox.parentElement.insertAdjacentHTML("afterend", warningHTML);
                        warningShown = true;
                    }
                    checkbox.checked = true;
                }
            })
        });
        //copy text-input value to clipboard 
        $('.mw-copytoclip').on("click", function() {
            var $button = $(this);
            var $inputField = $button.siblings('.mw-setting-form-input');
            var inputValue = $inputField.val();
            copyToClipboard(inputValue);
            $button.text(MooWoodleAppLocalizer.lang.Copied).prop('disabled', true);
            $('.mw-copytoclip').not($button).prop('disabled', false).text(MooWoodleAppLocalizer.lang.Copy);
        });
        $('.mw-setting-form-input').on("input", function() {
            var $inputField = $(this);
            var $button = $inputField.siblings('.mw-copytoclip');
            $button.prop('disabled', false).text(MooWoodleAppLocalizer.lang.Copy);
        });

        function copyToClipboard(text) {
            var tempInput = document.createElement("textarea");
            tempInput.style.position = 'absolute';
            tempInput.style.left = '-1000px';
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand("copy");
            document.body.removeChild(tempInput);
        }
        //multiple-checkboxs select/Deselect all
        const button = document.getElementById("selectDeselectButton");
        if (button != null) {
            button.addEventListener("click", function() {
                const checkedEnabledCheckboxes = document.querySelectorAll(".mw-toggle-checkbox:checked:enabled");
                const uncheckedCheckboxes = document.querySelectorAll(".mw-toggle-checkbox:not(:checked):enabled");
                if (checkedEnabledCheckboxes.length >= uncheckedCheckboxes.length) {
                    checkedEnabledCheckboxes.forEach(function(checkbox) {
                        if (!checkbox.disabled) checkbox.checked = false;
                    });
                } else {
                    uncheckedCheckboxes.forEach(function(checkbox) {
                        if (!checkbox.disabled) checkbox.checked = true;
                    });
                }
            });
        }
        //sso generat key
        const inputDiv = document.querySelector(".mw-textbox-input-wraper");
        const ssKeyInput = document.getElementById("moowoodle-sso-secret-key");
        if (ssKeyInput != null) {
            const generatButton = document.createElement("div");
            generatButton.innerHTML = '<button class="generat-key button-secondary" label="Generot Key" type="button">Generate</button>';
            inputDiv.appendChild(generatButton);
            let warningMessage = null;
            $(".generat-key").on("click", function() {
                const randomKey = generateRandomKey(8);
                ssKeyInput.value = randomKey;
                if (!warningMessage) {
                    warningMessage = document.createElement("div");
                    warningMessage.id = "warningMessage";
                    warningMessage.className = "mw-warning-massage";
                    warningMessage.style.color = "red";
                    warningMessage.innerText = "Remember to save your recent changes to ensure they're preserved.";
                    ssKeyInput.insertAdjacentElement("afterend", warningMessage);
                }
            });
            ssKeyInput.addEventListener("input", function() {
                if (warningMessage) {
                    warningMessage.remove();
                    warningMessage = null;
                }
            });

            function generateRandomKey(length) {
                const characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
                let key = "";
                for (let i = 0; i < length; i++) {
                    const randomIndex = Math.floor(Math.random() * characters.length);
                    key += characters.charAt(randomIndex);
                }
                return key;
            }
        }
    });
})(jQuery);
