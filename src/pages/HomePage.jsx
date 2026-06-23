import { useEffect } from 'react'
import Navbar from '../components/Navbar.jsx'
import HeroSection from '../components/HeroSection.jsx'
import AboutSection from '../components/AboutSection.jsx'
import MissionSection from '../components/MissionSection.jsx'
import ValuesSection from '../components/ValuesSection.jsx'
import LeadershipSection from '../components/LeadershipSection.jsx'
import WorkSection from '../components/WorkSection.jsx'
import FocusAreasSection from '../components/FocusAreasSection.jsx'
import ProgrammesSection from '../components/ProgrammesSection.jsx'
import FAQSection from '../components/FAQSection.jsx'
import JoinSection from '../components/JoinSection.jsx'
import Footer from '../components/Footer.jsx'
import BackToTop from '../components/BackToTop.jsx'

export default function HomePage() {
  useEffect(() => {
    // Import Bootstrap JS for navbar toggler functionality
    import('bootstrap/dist/js/bootstrap.bundle.min.js')
  }, [])

  return (
    <>
      <Navbar />
      <main id="main-content" role="main" aria-label="Main content">
        <HeroSection />
        <AboutSection />
        <MissionSection />
        <ValuesSection />
        <LeadershipSection />
        <WorkSection />
        <FocusAreasSection />
        <ProgrammesSection />
        <FAQSection />
        <JoinSection />
      </main>
      <Footer />
      <BackToTop />
    </>
  )
}
