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
                  non-partisan, non-governmental, and research-centred organisation focused on advancing good governance,
                  democratic accountability, and sustainable development in Nigeria and Africa.
                </p>
                <p>
                  We engage in research and analysis, policy advisory, policy advocacy, policy dialogue, civic engagement,
                  human capacity building, governance and policy consulting, and stakeholder engagement and partnerships to
                  promote national and regional development and tackle pressing governance and development challenges. Very
                  importantly, we create a platform that amplifies the integration of research and data into policy and
                  governance processes in driving sustainable development through evidence-based decision-making.
                </p>
                <p>
                  Through bold ideas, cutting-edge research, digital innovation, and inclusive dialogue, we empower
                  citizens and institutions to strengthen ethical public leadership, transparency, and accountability,
                  shaping policies and development outcomes.
                </p>
                <p>
                  CAGAD connects research to action, offering foresighted, practical policy solutions, analysis, and
                  recommendations that advance democracy, responsive governance, human security, and the{' '}
                  <abbr title="Sustainable Development Goals">SDGs</abbr>, while aligning our work and recommendations
                  with global best practices.
                </p>
                <aside className="mt-5" aria-labelledby="vision-heading">
                  <h3 className="mb-4" id="vision-heading">Our Vision</h3>
                  <div className="d-flex">
                    <i className="fas fa-eye me-4 mt-2 text-primary fa-2x" aria-hidden="true"></i>
                    <p>
                      Our vision is to become <strong>Nigeria and Africa's leading policy organisation</strong>, shaping governance,
                      policies, and development processes and outcomes through research, policy advisory, stakeholder
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
