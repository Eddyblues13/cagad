import FadeIn from './FadeIn.jsx'

export default function MissionSection() {
  return (
    <section className="section-padding bg-light" aria-labelledby="mission-heading" id="mission">
      <div className="container">
        <div className="row">
          <div className="col-lg-10 mx-auto">
            <FadeIn>
              <article>
                <h2 className="section-title center-title text-center" id="mission-heading">Our Mission</h2>
                <p className="text-center lead mb-4">
                  <strong>CAGAD's mission</strong> is to promote responsive governance, democratic accountability and sustainable
                  development. Through evidence-based research, policy advocacy, policy dialogue, and stakeholder
                  collaborations and partnerships, we seek to strengthen governance and policy processes, deepen citizens'
                  participation in governance, enhance evidence-based decision-making, promote inclusive governance,
                  improve human security, and advance sustainable development in Nigeria and Africa.
                </p>
              </article>
            </FadeIn>
          </div>
        </div>
      </div>
    </section>
  )
}
