import { useEffect, useMemo } from 'react'
import Particles, { initParticlesEngine } from '@tsparticles/react'
import { loadSlim } from '@tsparticles/slim'
import { useState } from 'react'

export default function HeroSection() {
  const [init, setInit] = useState(false)

  useEffect(() => {
    initParticlesEngine(async (engine) => {
      await loadSlim(engine)
    }).then(() => {
      setInit(true)
    })
  }, [])

  const particlesOptions = useMemo(
    () => ({
      fpsLimit: 60,
      particles: {
        number: {
          value: 80,
          density: {
            enable: true,
            area: 800,
          },
        },
        color: {
          value: '#ffffff',
        },
        shape: {
          type: 'circle',
        },
        opacity: {
          value: 0.5,
        },
        size: {
          value: { min: 1, max: 3 },
        },
        links: {
          enable: true,
          distance: 150,
          color: '#ffffff',
          opacity: 0.4,
          width: 1,
        },
        move: {
          enable: true,
          speed: 2,
          direction: 'none',
          outModes: {
            default: 'out',
          },
        },
      },
      interactivity: {
        events: {
          onHover: {
            enable: true,
            mode: 'grab',
          },
          onClick: {
            enable: true,
            mode: 'push',
          },
        },
      },
      detectRetina: true,
    }),
    []
  )

  const handleScrollTo = (e, target) => {
    e.preventDefault()
    const el = document.querySelector(target)
    if (el) el.scrollIntoView({ behavior: 'smooth' })
  }

  return (
    <section
      id="home"
      className="hero-section"
      aria-label="Welcome to CAGAD - Advancing Governance and Development in Africa"
      itemScope
      itemType="https://schema.org/WPHeader"
    >
      {init && <Particles id="tsparticles" options={particlesOptions} aria-hidden="true" />}
      <div className="container hero-content">
        <div className="row">
          <div className="col-lg-8 mx-auto text-center">
            <h1 className="hero-title" itemProp="headline">
              Advancing Governance and Development in Africa
            </h1>
            <p className="lead mb-5" itemProp="description">
              An independent, non-profit, non-governmental, and research-centred think tank, focused on advancing good
              governance, accountability, and development in Nigeria and Africa.
            </p>
            <div className="d-inline-flex gap-3 flex-wrap justify-content-center" role="group" aria-label="Call to action buttons">
              <a
                href="#about"
                className="btn btn-lg btn-primary"
                onClick={(e) => handleScrollTo(e, '#about')}
                role="button"
                aria-label="Learn more about CAGAD"
              >
                Learn More
              </a>
              <a
                href="#join"
                className="btn btn-lg btn-outline-light"
                onClick={(e) => handleScrollTo(e, '#join')}
                role="button"
                aria-label="Get involved and volunteer with CAGAD"
              >
                Get Involved
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
