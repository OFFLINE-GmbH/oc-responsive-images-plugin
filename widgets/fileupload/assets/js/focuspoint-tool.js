window.addEventListener('load', function () {
    initFocuspointTool()
    const observableNode = document.querySelector('.upload-files-container')
    const observerConfig = {attributes: true, childList: true, subtree: true}
    let backendObserver = new MutationObserver(initFocuspointTool)
    backendObserver.observe(observableNode, observerConfig)
})

function initFocuspointTool () {
    const uploadedImages = document.querySelectorAll('.upload-object')

    var focuspointImage = new Object()

    Array.prototype.forEach.call(uploadedImages, el => {
        // Click on the uploaded image will open the customized config form
        el.addEventListener('click', function () {

            // Observe the document for changes like modal popups
            const observerConfig = {attributes: true, childList: true, subtree: true}
            let modalObserver = new MutationObserver(function () {
                let fileuploadConfigForm = document.querySelector('.fileupload-config-form')

                // Check if fileupload config form was loaded
                if (fileuploadConfigForm) {

                    let el = fileuploadConfigForm.querySelector('.img-responsive')
                    let point = fileuploadConfigForm.querySelector('.focuspoint')

                    // Load image dimensions
                    focuspointImage.dimensions = el.getBoundingClientRect()

                    // Set focuspoint-attributes to default x=0, y=0 (center, center)
                    focuspointImage.focuspoint = {
                        x: document.getElementById('x-axis').value || 50,
                        y: document.getElementById('y-axis').value || 50
                    }

                    // Set the point to the center of the image
                    point.style.left = focuspointImage.focuspoint.x + '%'
                    point.style.top = focuspointImage.focuspoint.y + '%'

                    el.addEventListener('click', function (e) {

                        // Reload dimensions to prevent loading issues
                        focuspointImage.dimensions = el.getBoundingClientRect()

                        // Move the point to the clicked area
                        point.style.left = e.offsetX + 'px'
                        point.style.top = e.offsetY + 'px'

                        // Set the focuspoint values
                        focuspointImage.focuspoint = {
                            x: 100 / focuspointImage.dimensions.width * e.offsetX,
                            y: 100 / focuspointImage.dimensions.height * e.offsetY
                        }

                        // Set values to Axis-Fields
                        document.getElementById('x-axis').value = focuspointImage.focuspoint.x
                        document.getElementById('y-axis').value = focuspointImage.focuspoint.y
                    })
                }
            })

            // Trigger observer
            modalObserver.observe(document, observerConfig)
        })
    })
}

