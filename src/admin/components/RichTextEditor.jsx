import { useEffect, useRef } from 'react'
import { uploadEditorImage } from '../lib/api.js'

// TinyMCE (GPL build) comes from jsDelivr on first use, so it never weighs on the public site.
const TINYMCE_SRC = 'https://cdn.jsdelivr.net/npm/tinymce@7.9.1/tinymce.min.js'
let tinymceLoader = null

function loadTinyMce() {
  tinymceLoader ??= new Promise((resolve, reject) => {
    if (window.tinymce) {
      resolve(window.tinymce)
      return
    }

    const script = document.createElement('script')
    script.src = TINYMCE_SRC
    script.referrerPolicy = 'origin'
    script.onload = () => resolve(window.tinymce)
    script.onerror = () => {
      tinymceLoader = null
      reject(new Error('The editor couldn’t be loaded. Check your connection and reload the page.'))
    }
    document.head.append(script)
  })

  return tinymceLoader
}

const CONTENT_STYLE = `
  body { font-family: 'Open Sans', system-ui, sans-serif; font-size: 17px; line-height: 1.75; color: #333; max-width: 760px; margin: 1.25rem auto; }
  h2, h3, h4 { font-family: Montserrat, sans-serif; color: #003366; }
  img { max-width: 100%; height: auto; border-radius: 8px; }
  figure { margin: 1.5rem 0; }
  figcaption { font-size: 14px; color: #6b7280; text-align: center; }
  blockquote { border-left: 4px solid #006633; margin-left: 0; padding-left: 1rem; color: #4b5563; font-style: italic; }
  table { border-collapse: collapse; width: 100%; } td, th { border: 1px solid #ddd; padding: 6px 10px; }
`

/**
 * The post body editor. The parent reads the content through `editorRef`
 * (getContent, uploadImages) when saving rather than on every keystroke.
 * Images added or pasted are uploaded to Cloudinary straight away.
 */
export default function RichTextEditor({ id, initialValue, editorRef, onDirty, onSave, onLoadError }) {
  const textareaRef = useRef(null)
  const callbacks = useRef({ onDirty, onSave, onLoadError })

  useEffect(() => {
    callbacks.current = { onDirty, onSave, onLoadError }
  })

  useEffect(() => {
    let isCancelled = false
    let editor = null

    loadTinyMce()
      .then((tinymce) => {
        if (isCancelled) return undefined

        return tinymce.init({
          target: textareaRef.current,
          license_key: 'gpl',
          promotion: false,
          branding: false,
          menubar: false,
          plugins: 'autolink autoresize link lists image table code wordcount',
          toolbar: 'undo redo | blocks | bold italic underline | bullist numlist blockquote | link image table | removeformat code',
          toolbar_mode: 'sliding',
          toolbar_sticky: true,
          toolbar_sticky_offset: 64,
          block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
          min_height: 520,
          autoresize_bottom_margin: 40,
          // The autoresize plugin sets the body's side padding inline, overriding content_style.
          autoresize_overflow_padding: 24,
          content_css: ['default', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400&display=swap'],
          content_style: CONTENT_STYLE,
          image_caption: true,
          image_dimensions: false,
          automatic_uploads: true,
          paste_data_images: true,
          images_file_types: 'jpg,jpeg,png,gif,webp',
          images_upload_handler: (blobInfo, progress) =>
            uploadEditorImage(blobInfo.blob(), blobInfo.filename(), progress).catch((error) => {
              throw { message: `Image upload failed: ${error.message}`, remove: true }
            }),
          relative_urls: false,
          convert_urls: false,
          link_assume_external_targets: 'https',
          link_target_list: [
            { title: 'Same tab', value: '' },
            { title: 'New tab', value: '_blank' },
          ],
          setup: (instance) => {
            instance.on('input change undo redo', () => callbacks.current.onDirty?.())
            instance.addShortcut('meta+s', 'Save post', () => callbacks.current.onSave?.())
          },
        })
      })
      .then((editors) => {
        if (!editors) return
        ;[editor] = editors

        if (isCancelled) {
          editor.remove()
          return
        }

        editorRef.current = editor
      })
      .catch((error) => callbacks.current.onLoadError?.(error))

    return () => {
      isCancelled = true
      editor?.remove()
      editorRef.current = null
    }
  }, [editorRef])

  return <textarea ref={textareaRef} id={id} defaultValue={initialValue} rows={20} className="form-control" />
}
