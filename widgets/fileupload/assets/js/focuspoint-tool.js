window.addEventListener('load', function () {
    initFocuspointTool()
    var observableNode = document.querySelector('.upload-files-container')
    if (!observableNode) {
        observableNode = document.querySelector('.rowlink')
    }
    if (!observableNode) {
        return;
    }

    var observerConfig = {childList: true, subtree: true}
    var backendObserver = new MutationObserver(initFocuspointTool)
    backendObserver.observe(observableNode, observerConfig)
})

function initFocuspointTool () {
    var uploadedImages = document.querySelectorAll('.upload-object')
    var rowlinks = document.querySelectorAll('.rowlink')

    if (uploadedImages.length > 0) {
        Array.prototype.forEach.call(uploadedImages, function (el) {
            el.addEventListener('click', onImageClick)
        })
    } else {
        // If multi modal reverse initFocuspoint
        Array.prototype.forEach.call(rowlinks, function (el) {
            el.addEventListener('click', function () {
                // Observe the document for modal popup
                var observerConfig = {childList: true, subtree: true}
                var modalObserver = new MutationObserver(initFocuspointTool)
                // Trigger observer
                modalObserver.observe(document, observerConfig)
            })
        })
    }
}

/**
 * Event handler for images in the upload widget.
 */
function onImageClick () {
    waitForConfigForm(0, function () {
        // Abort if the OFFLINE.ResponsiveImages fields are missing.
        if (!document.getElementById('x-axis')) {
            return
        }

        var fileuploadConfigForm = document.querySelector('.fileupload-config-form')
        var img = fileuploadConfigForm.querySelector('.img-responsive')
        var point = fileuploadConfigForm.querySelector('.focuspoint')
        var xAxisInput = document.getElementById('x-axis')
        var yAxisInput = document.getElementById('y-axis')

        var focuspointImage = {
            dimensions: img.getBoundingClientRect(),
            focuspoint: {
                x: xAxisInput.value || 50,
                y: yAxisInput.value || 50
            }
        }

        // Set initial values.
        xAxisInput.value = focuspointImage.focuspoint.x
        yAxisInput.value = focuspointImage.focuspoint.y

        // Set marker position.
        point.style.left = focuspointImage.focuspoint.x + '%'
        point.style.top = focuspointImage.focuspoint.y + '%'
        point.style.display = 'block'

        img.addEventListener('click', function (e) {
            // Reload dimensions to prevent loading issues
            focuspointImage.dimensions = img.getBoundingClientRect()

            // Move the point to the clicked area
            point.style.left = e.offsetX + 'px'
            point.style.top = e.offsetY + 'px'

            // Set the focuspoint values
            focuspointImage.focuspoint = {
                x: 100 / focuspointImage.dimensions.width * e.offsetX,
                y: 100 / focuspointImage.dimensions.height * e.offsetY
            }

            // Update values to Axis-Fields
            xAxisInput.value = focuspointImage.focuspoint.x
            yAxisInput.value = focuspointImage.focuspoint.y
        })
    })
}

/**
 * Wait until the config form is added to the DOM.
 */
function waitForConfigForm (tries, callback) {
    if (document.querySelector('.fileupload-config-form')) {
        return callback()
    }
    if (tries >= 10) {
        console.error('[OFFLINE.ResponsiveImages] Config form failed to load.')
        return false
    }
    setTimeout(function () {
        waitForConfigForm(++ tries, callback)
    }, 500)
}