import { useEffect, useRef, useState } from 'react'

const MENU_GAP = 4
const ROOM_NEEDED_BELOW = 220

/**
 * A button that opens a Bootstrap-styled menu. The menu is positioned against
 * the viewport, so it isn't clipped inside scrolling tables. Any click inside
 * the menu closes it.
 */
export default function Dropdown({ label, ariaLabel, buttonClassName, menuClassName = '', children }) {
  const [position, setPosition] = useState(null)
  const buttonRef = useRef(null)
  const menuRef = useRef(null)
  const isOpen = position !== null

  useEffect(() => {
    if (!isOpen) return undefined

    const close = () => setPosition(null)
    const onPointerDown = (event) => {
      if (!menuRef.current?.contains(event.target) && !buttonRef.current?.contains(event.target)) close()
    }
    const onKeyDown = (event) => {
      if (event.key === 'Escape') {
        close()
        buttonRef.current?.focus()
      }
    }

    document.addEventListener('pointerdown', onPointerDown)
    document.addEventListener('keydown', onKeyDown)
    window.addEventListener('resize', close)
    window.addEventListener('scroll', close, true)

    return () => {
      document.removeEventListener('pointerdown', onPointerDown)
      document.removeEventListener('keydown', onKeyDown)
      window.removeEventListener('resize', close)
      window.removeEventListener('scroll', close, true)
    }
  }, [isOpen])

  function toggle() {
    if (isOpen) {
      setPosition(null)
      return
    }

    const rect = buttonRef.current.getBoundingClientRect()
    const opensUpward = window.innerHeight - rect.bottom < ROOM_NEEDED_BELOW && rect.top > ROOM_NEEDED_BELOW

    setPosition({
      right: window.innerWidth - rect.right,
      ...(opensUpward ? { bottom: window.innerHeight - rect.top + MENU_GAP } : { top: rect.bottom + MENU_GAP }),
    })
  }

  return (
    <>
      <button
        ref={buttonRef}
        type="button"
        className={buttonClassName}
        aria-label={ariaLabel}
        aria-haspopup="true"
        aria-expanded={isOpen}
        onClick={toggle}
      >
        {label}
      </button>
      {isOpen && (
        <div
          ref={menuRef}
          className={`dropdown-menu show ${menuClassName}`}
          style={{ position: 'fixed', left: 'auto', margin: 0, ...position }}
          onClick={() => setPosition(null)}
        >
          {children}
        </div>
      )}
    </>
  )
}
