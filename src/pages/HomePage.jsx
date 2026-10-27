import { useEffect } from 'react'
import { useLocation } from 'react-router'
import HeroSection from '../components/HeroSection.jsx'
import AboutSection from '../components/AboutSection.jsx'
import MissionSection from '../components/MissionSection.jsx'
import ValuesSection from '../components/ValuesSection.jsx'
import LeadershipSection from '../components/LeadershipSection.jsx'
import WorkSection from '../components/WorkSection.jsx'
import FocusAreasSection from '../components/FocusAreasSection.jsx'
import ProgrammesSection from '../components/ProgrammesSection.jsx'
import LatestPostsSection from '../components/LatestPostsSection.jsx'
import FAQSection from '../components/FAQSection.jsx'
import JoinSection from '../components/JoinSection.jsx'

export default function HomePage() {
  const { hash } = useLocation()

  // Arriving from another page via a section link (e.g. /#about): scroll to it.
  useEffect(() => {
    if (hash) {
      document.getElementById(hash.slice(1))?.scrollIntoView({ behavior: 'smooth' })
    }
  }, [hash])

  return (
    <>
      <HeroSection />
      <AboutSection />
      <MissionSection />
      <ValuesSection />
      <LeadershipSection />
      <WorkSection />
      <FocusAreasSection />
      <ProgrammesSection />
      <LatestPostsSection />
      <FAQSection />
      <JoinSection />
    </>
  )
}
