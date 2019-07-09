window.addEventListener('load', function () {
    initFocuspointTool()
    let observableNode = document.querySelector('.upload-files-container') ? document.querySelector('.upload-files-container') : document.querySelector('.rowlink')
    const observerConfig = {childList: true, subtree: true}
    let backendObserver = new MutationObserver(initFocuspointTool)
    backendObserver.observe(observableNode, observerConfig)
})

function initFocuspointTool () {

    const uploadedImages = document.querySelectorAll('.upload-object')
    const rowlinks = document.querySelectorAll('.rowlink')

    var focuspointImage = {}

    console.log(uploadedImages)

    if (uploadedImages.length > 0) {
        Array.prototype.forEach.call(uploadedImages, el => {
                // Click on the uploaded image will open the customized config form
                el.addEventListener('click', () => {
                    // Observe the document for modal popup
                    const observerConfig = {childList: true, subtree: true}
                    let modalObserver = new MutationObserver(() => {
                        let fileuploadConfigForm = document.querySelector('.fileupload-config-form')

                        // Check if fileupload config form was loaded and foucspoint is enabled (x-axis field)
                        if (fileuploadConfigForm && document.getElementById('x-axis')) {

                            let img = fileuploadConfigForm.querySelector('.img-responsive')
                            let point = fileuploadConfigForm.querySelector('.focuspoint')

                            // Load image dimensions
                            focuspointImage.dimensions = img.getBoundingClientRect()

                            // Set focuspoint-attributes to default x=50, y=50 (center, center)
                            focuspointImage.focuspoint = {
                                x: document.getElementById('x-axis').value || 50,
                                y: document.getElementById('y-axis').value || 50
                            }

                            // Initial set values to Axis-Fields
                            document.getElementById('x-axis').value = focuspointImage.focuspoint.x
                            document.getElementById('y-axis').value = focuspointImage.focuspoint.y

                            // Set the point to the center of the image
                            point.style.left = focuspointImage.focuspoint.x + '%'
                            point.style.top = focuspointImage.focuspoint.y + '%'

                            img.addEventListener('click', (e) => {

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
                                document.getElementById('x-axis').value = focuspointImage.focuspoint.x
                                document.getElementById('y-axis').value = focuspointImage.focuspoint.y
                            })
                        }
                    })

                    // Trigger observer
                    modalObserver.observe(document, observerConfig)
                })
            }
        )
    } else {
        // If multi modal reverse initFocuspoint
        Array.prototype.forEach.call(rowlinks, el => {
            el.addEventListener('click', () => {
                // Observe the document for modal popup
                const observerConfig = {childList: true, subtree: true}
                let modalObserver = new MutationObserver(initFocuspointTool)
                // Trigger observer
                modalObserver.observe(document, observerConfig)
            })
        })
    }
}