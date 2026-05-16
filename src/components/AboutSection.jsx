import FadeIn from './FadeIn.jsx'

export default function AboutSection() {
  return (
    <section id="about" className="section-padding" aria-labelledby="about-heading" itemScope itemType="https://schema.org/AboutPage">
      <div className="container">
        <div className="row">
          <div className="col-lg-6">
            <FadeIn>
              <article itemProp="mainContentOfPage">
                <h2 className="section-title" id="about-heading" itemProp="name">About CAGAD</h2>
                <p itemProp="description">
                  The <strong>Centre for Advancement of Governance and Development (CAGAD)</strong> is an independent, non-profit,
                  non-partisan, non-governmental, research-centred organisation, focused on advancing good governance,
                  democratic accountability, and sustainable development in Nigeria and Africa.
                </p>
                <p>
                  We engage in research and analysis, policy advisory and advocacy, policy dialogue, governance and policy
                  consulting, civic and stakeholder engagement and partnerships to address critical challenges and promote
                  national and regional development.
                </p>
                <p>
                  Through bold ideas, evidence-based research, and digital innovation, we work to strengthen ethical public
                  leadership, deepen transparency and accountability in governance, and shape public policies and development
                  outcomes.
                </p>
                <p>
                  CAGAD connects research to action, offering foresighted, practical policy solutions, analysis, and
                  recommendations that strengthen democracy and responsive governance, improve security, and accelerate
                  progress on the Sustainable Development Goals (SDGs).
                </p>
                <aside className="mt-5" aria-labelledby="vision-heading">
                  <h3 className="mb-4" id="vision-heading">Our Vision</h3>
                  <div className="d-flex">
                    <i className="fas fa-eye me-4 mt-2 text-primary fa-2x" aria-hidden="true"></i>
                    <p>
                      Our vision is to become <strong>Nigeria and Africa's leading policy organisation</strong>, shaping governance,
                      public policies, and development processes through research, policy advisory, stakeholder
                      engagement, and digital innovation.
                    </p>
                  </div>
                </aside>
              </article>
            </FadeIn>
          </div>
          <div className="col-lg-6">
            <FadeIn>
              <figure>
                <img
                  src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                  alt="CAGAD team members engaged in a collaborative policy research discussion around a table"
                  className="img-fluid rounded shadow-lg"
                  loading="lazy"
                  width="800"
                  height="534"
                  itemProp="image"
                />
              </figure>
            </FadeIn>
          </div>
        </div>
      </div>
    </section>
  )
}
