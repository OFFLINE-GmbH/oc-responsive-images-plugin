addEventListener('page:loaded', function () {
    // Search for new file upload widgets every time an ajax request is
    // done or a form change event is triggered.
    addEventListener('ajax:update-complete', init)
})

addEventListener('page:unload', function () {
    removeEventListener('ajax:request-success', init)
})

function init(e) {
    if (!e.detail.context.handler.includes('onLoadAttachmentConfig')) {
        return
    }

    var fileuploadConfigForm = document.querySelector('.fileupload-config-form')

    var header = fileuploadConfigForm.querySelector('.file-upload-modal-image-header')
    header.style.position = 'relative'

    var img = header.querySelector('img')

    var focuspoint = document.createElement('div')
    focuspoint.style.background = 'red'
    focuspoint.style.width = '13px'
    focuspoint.style.height = '13px'
    focuspoint.style.position = 'absolute'
    focuspoint.style.borderRadius = '100%'
    focuspoint.style.outline = '5px rgba(255, 0, 0, 0.4) solid'
    focuspoint.style.display = 'none'

    header.appendChild(focuspoint)

    var xAxisInput = document.querySelector('.focuspoint-x-axis input')
    var yAxisInput = document.querySelector('.focuspoint-y-axis input')

    var focuspointImage = {
        dimensions: img.getBoundingClientRect(),
        focuspoint: {
            x: xAxisInput.value !== '' ? xAxisInput.value : 50,
            y: yAxisInput.value !== '' ? yAxisInput.value : 50
        }
    }

    // Returns the base offset of the image relative to the header contaner.
    function baseOffset() {
        return parseInt(getComputedStyle(img).marginLeft)
    }

    img.addEventListener('load', function (e) {
        setTimeout(() => {
            focuspointImage.dimensions = img.getBoundingClientRect()
            // Set marker position.
            focuspoint.style.left = baseOffset() + (focuspointImage.dimensions.width / 100 * focuspointImage.focuspoint.x) + 'px'
            focuspoint.style.top = focuspointImage.dimensions.height / 100 * focuspointImage.focuspoint.y + 'px'
            focuspoint.style.display = 'block'
        }, 200) // Let the "scale" animation finish.
    })

    img.addEventListener('click', function (e) {
        // Reload dimensions to prevent loading issues
        focuspointImage.dimensions = img.getBoundingClientRect()

        // Move the point to the clicked area
        focuspoint.style.left = baseOffset() + e.offsetX + 'px'
        focuspoint.style.top = e.offsetY + 'px'

        // Set the focuspoint values
        focuspointImage.focuspoint = {
            x: (100 / focuspointImage.dimensions.width * e.offsetX).toFixed(2),
            y: (100 / focuspointImage.dimensions.height * e.offsetY).toFixed(2)
        }

        // Update values to Axis-Fields
        xAxisInput.value = focuspointImage.focuspoint.x
        yAxisInput.value = focuspointImage.focuspoint.y
    })
}

/**
 * Search for new upload widgets and add the click handler to them.
 */
function initFocuspointTool() {
    var uploadedImages = document.querySelectorAll('.upload-object:not(.focuspoint-widget)')
    console.log('init', uploadedImages)
    if (uploadedImages.length > 0) {
        Array.prototype.forEach.call(uploadedImages, function (el) {
            el.addEventListener('click', onImageClick)
            el.classList.add('focuspoint-widget')
        })
    }
}

/**
 * Event handler for images in the upload widget.
 */
function onImageClick(e) {
    // Ignore clicks on the "remove" icon.
    if (e.target.classList.contains('icon-times')) {
        return
    }

    console.log('clicked')

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
function waitForConfigForm(tries, callback) {
    if (document.querySelector('.fileupload-config-form')) {
        return callback()
    }
    if (tries >= 10) {
        console.error('[OFFLINE.ResponsiveImages] Config form failed to load.')
        return false
    }
    setTimeout(function () {
        waitForConfigForm(++tries, callback)
    }, 500)
}
