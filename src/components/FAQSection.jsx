import { useState } from 'react'
import FadeIn from './FadeIn.jsx'

const faqs = [
  {
    question: 'What is CAGAD?',
    answer:
      'CAGAD (Centre for Advancement of Governance and Development) is an independent, non-profit, non-partisan, and research-centred think tank based in Abuja, Nigeria. It focuses on advancing good governance, democratic accountability, security, and sustainable development in Nigeria and across Africa through evidence-based research, policy advocacy, and stakeholder engagement.',
  },
  {
    question: 'What does CAGAD do?',
    answer:
      'CAGAD engages in research and analysis, policy advisory, policy advocacy, policy dialogue, civic engagement, human capacity building, governance consulting, and stakeholder partnerships to promote national and regional development. The organisation monitors elections, democracy, accountability, and security across African regions, and provides evidence-based policy recommendations to governments and institutions.',
  },
  {
    question: "What are CAGAD's focus areas?",
    answer:
      "CAGAD focuses on six key areas: (1) Governance – promoting good governance and public leadership integrity; (2) Accountability – monitoring public expenditure and tracking government performance; (3) Security – analysing security incidents and improving human safety; (4) Democracy – deepening democratic consolidation and citizen participation; (5) Peacebuilding – fostering conflict resolution in fragile contexts; and (6) Sustainable Development – promoting the SDGs and inclusive development.",
  },
  {
    question: "What is CAGAD's mission?",
    answer:
      "CAGAD's mission is to promote responsive governance, democratic accountability, and sustainable development. Through evidence-based research, policy advocacy, policy dialogue, and stakeholder collaborations, CAGAD seeks to strengthen governance and policy processes, deepen citizens' participation, enhance evidence-based decision-making, promote inclusive governance, improve human security, and advance sustainable development in Nigeria and Africa.",
  },
  {
    question: "What is CAGAD's vision?",
    answer:
      "CAGAD's vision is to become Nigeria and Africa's leading policy organisation, shaping governance, policies, and development processes and outcomes through research, policy advisory, stakeholder engagement, and digital innovation.",
  },
  {
    question: 'How can I volunteer or join CAGAD?',
    answer:
      'You can volunteer with CAGAD by filling out the application form on the website at cagad.org. The form requires your personal details, skills, areas of interest (such as Research, Advocacy, Community Engagement, Technology & Innovation, Administration, Communications, or Fundraising), and how many hours per week you can commit. For inquiries, email info@cagad.org or call +2349123733985.',
  },
  {
    question: 'Where is CAGAD located?',
    answer:
      'CAGAD is headquartered in Abuja, Nigeria. You can contact CAGAD via email at info@cagad.org or by phone at +2349123733985.',
  },
  {
    question: 'What programmes does CAGAD run?',
    answer:
      'CAGAD runs multiple programmes including: Elections Credibility Monitoring, Democracy Monitoring, Accountability Monitoring, Security Monitoring, Civic Engagement, West Africa Programme, and International Affairs Programme.',
  },
]

export default function FAQSection() {
  const [openIndex, setOpenIndex] = useState(null)

  const toggleFaq = (index) => {
    setOpenIndex(openIndex === index ? null : index)
  }

  return (
    <section id="faq" className="section-padding" aria-labelledby="faq-heading">
      <div className="container">
        <div className="row">
          <div className="col-lg-10 mx-auto">
            <FadeIn>
              <h2 className="section-title center-title text-center mb-5" id="faq-heading">
                Frequently Asked Questions
              </h2>
              <p className="text-center lead mb-5">
                Find answers to common questions about CAGAD, our mission, programmes, and how to get involved.
              </p>
            </FadeIn>
            <div className="accordion" id="faqAccordion" role="list" aria-label="Frequently asked questions about CAGAD">
              {faqs.map((faq, index) => (
                <FadeIn key={index}>
                  <div
                    className="faq-item"
                    role="listitem"
                    itemScope
                    itemProp="mainEntity"
                    itemType="https://schema.org/Question"
                  >
                    <button
                      className={`faq-question ${openIndex === index ? 'active' : ''}`}
                      onClick={() => toggleFaq(index)}
                      aria-expanded={openIndex === index}
                      aria-controls={`faq-answer-${index}`}
                      id={`faq-question-${index}`}
                    >
                      <span itemProp="name">{faq.question}</span>
                      <i className={`fas fa-chevron-down faq-chevron ${openIndex === index ? 'rotated' : ''}`} aria-hidden="true"></i>
                    </button>
                    <div
                      id={`faq-answer-${index}`}
                      className={`faq-answer ${openIndex === index ? 'open' : ''}`}
                      role="region"
                      aria-labelledby={`faq-question-${index}`}
                      itemScope
                      itemProp="acceptedAnswer"
                      itemType="https://schema.org/Answer"
                    >
                      <p itemProp="text">{faq.answer}</p>
                    </div>
                  </div>
                </FadeIn>
              ))}
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
