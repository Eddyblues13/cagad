import { useState } from 'react'

/**
 * Pick a cover by clicking or dropping an image on the box. Shows the chosen
 * file straight away, otherwise the post's current cover.
 */
export default function CoverImageField({ currentUrl, file, onFileChange, error }) {
  const [previewUrl, setPreviewUrl] = useState(null)
  const [isDragOver, setIsDragOver] = useState(false)
  const shownUrl = file ? previewUrl : currentUrl

  function choose(nextFile) {
    if (!nextFile?.type.startsWith('image/')) return

    if (previewUrl) URL.revokeObjectURL(previewUrl)
    setPreviewUrl(URL.createObjectURL(nextFile))
    onFileChange(nextFile)
  }

  function onDrop(event) {
    event.preventDefault()
    setIsDragOver(false)
    choose(event.dataTransfer.files[0])
  }

  return (
    <div className="cover-field mb-3">
      <input
        type="file"
        id="cover_image"
        className="visually-hidden"
        accept="image/jpeg,image/png,image/webp,image/gif"
        aria-describedby="cover-help"
        onChange={(event) => choose(event.target.files[0])}
      />
      <label
        htmlFor="cover_image"
        className={`cover-drop${shownUrl ? ' has-image' : ''}${isDragOver ? ' is-dragover' : ''}`}
        onDragEnter={(event) => {
          event.preventDefault()
          setIsDragOver(true)
        }}
        onDragOver={(event) => event.preventDefault()}
        onDragLeave={() => setIsDragOver(false)}
        onDrop={onDrop}
      >
        {shownUrl ? (
          <>
            <img src={shownUrl} alt="" />
            <span className="visually-hidden">Replace the cover image</span>
          </>
        ) : (
          <>
            <i className="fa-solid fa-cloud-arrow-up" aria-hidden="true" />
            <span>
              <strong>Click to upload</strong> or drag an image here
            </span>
            <span className="small">JPG, PNG, WebP or GIF up to 5 MB</span>
          </>
        )}
      </label>
      {error && <div className="field-error">{error}</div>}
      <div id="cover-help" className="form-text">
        Landscape images around 1600 × 900 look best.
      </div>
    </div>
  )
}
