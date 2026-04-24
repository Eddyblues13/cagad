import FadeIn from './FadeIn.jsx'

const workItems = [
  { icon: 'fas fa-search', title: 'Research & Analysis', desc: 'Conducting cutting-edge evidence-based research on governance and development challenges.' },
  { icon: 'fas fa-briefcase', title: 'Policy Advisory', desc: 'Providing timely, actionable, evidence-based policy advice and recommendations to governments, policymakers, and institutions.' },
  { icon: 'fas fa-bullhorn', title: 'Policy Advocacy', desc: 'Engaging in targeted advocacy campaigns that promote transparency and good governance.' },
  { icon: 'fas fa-comments', title: 'Policy Dialogue', desc: 'Facilitating constructive dialogues between stakeholders, citizens and policymakers on critical governance issues and creating a platform that enables citizens and stakeholders to co-create solutions.' },
  { icon: 'fas fa-handshake', title: 'Civic Engagement', desc: 'Empowering citizens and communities through civic awareness, engagement and education programmes, capacity building, and digital enlightenment campaigns.' },
  { icon: 'fas fa-users', title: 'Stakeholder Engagement', desc: 'Building strong partnerships with policymakers, institutions, civic actors, stakeholders, and development partners.' },
]

export default function WorkSection() {
  return (
    <section id="work" className="section-padding" aria-labelledby="work-heading">
      <div className="container">
        <h2 className="section-title center-title text-center mb-5" id="work-heading">Our Work</h2>
        <div className="row" role="list" aria-label="CAGAD areas of work">
          {workItems.map((item) => (
            <div className="col-md-6 col-lg-4 mb-4" key={item.title} role="listitem">
              <FadeIn>
                <article className="focus-card card h-100" aria-label={item.title}>
                  <div className="card-body text-center p-5">
                    <div className="card-icon" aria-hidden="true">
                      <i className={item.icon}></i>
                    </div>
                    <h3 className="card-title h4">{item.title}</h3>
                    <p className="card-text">{item.desc}</p>
                  </div>
                </article>
              </FadeIn>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
