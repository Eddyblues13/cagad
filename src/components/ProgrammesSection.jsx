import FadeIn from './FadeIn.jsx'

const leftProgrammes = [
  {
    title: 'Elections Credibility Monitoring',
    desc: 'Monitoring the credibility of elections in Nigeria and Africa, and providing independent, evidence-based, and non-partisan assessments of elections and electoral processes at regional, national and subnational levels.',
  },
  {
    title: 'Democracy Monitoring Programme',
    desc: "Tracking the health of democracy in Nigeria and Africa by monitoring trends in constitutional governance, the rule of law, civic freedom, civil liberties, democratic institutions, and citizens' participation in governance.",
  },
  {
    title: 'Accountability Monitoring Programme',
    desc: 'Monitoring and tracking government performance, transparent budgeting, fiscal responsibility, service delivery on promises, anti-corruption initiatives and evidence for abuse of office, public trust and institutional credibility.',
  },
  {
    title: 'Security Monitoring Programme',
    desc: 'Monitoring and tracking security incidents, analysing conflict and insecurity trends, including terrorism, violent extremism, organised crime, and communal violence, providing early warning, conflict mapping, policy briefs, and advisory guides for governments, institutions, communities, and security actors.',
  },
  {
    title: 'Civic Engagement Programme',
    desc: "Building platforms and tools for citizens' engagement, including youth, women, and underrepresented groups, to effectively participate in governance processes, drive change, and co-create solutions.",
  },
]

const rightProgrammes = [
  {
    title: 'Central Africa Programme',
    desc: 'Focusing on political, economic, and social developments across the Central African subregion.',
  },
  {
    title: 'East Africa Programme',
    desc: 'Analysing and providing independent assessment on political, economic, and social developments across the East African subregion.',
  },
  {
    title: 'North Africa Programme',
    desc: 'Driving independent thinking, analysing, and evaluating political, economic, and social developments in the North African region.',
  },
  {
    title: 'Sahel Programme',
    desc: 'Monitoring, assessing and evaluating political, economic, and social developments across the Sahel region, including broader regional integration, geopolitical dynamics, political stability, and regional and international relations with a particular focus on the Alliance of Sahel States.',
  },
  {
    title: 'Southern Africa Programme',
    desc: 'Focusing on political, economic, and social developments across the Southern African subregion.',
  },
  {
    title: 'West Africa Programme',
    desc: 'Monitoring and evaluating political, economic, and social developments across the West African subregion, including regional integration, governance, and democratic stability.',
  },
  {
    title: 'International Affairs Programme',
    desc: "Providing independent thinking and conducting evidence-based policy research and analysis on how international regimes, geopolitics, and global events impact Africa, providing policy advice and recommendations on how African states and institutions could effectively promote national and regional interests and development in a competing and divided world system.",
  },
  {
    title: 'Nigeria in Africa and the World Programme',
    desc: "Serves as a hub for strategic, long-term, independent thinking, analysis, and research about Nigeria's governance and development realities, foreign policy choices, and relationships with Africa and the world.",
  },
  {
    title: 'Regional Security Programme',
    desc: 'Monitor regional dynamics and provide cutting-edge, policy-relevant research on the regional security landscape, focusing on the root causes and impact of non-state violence.',
  },
  {
    title: 'Regional Institutions and Governance Programme',
    desc: 'Providing evidence-based research and policy analyses on the role and policies of regional institutions in Africa and how regional actors, such as the African Union (AU), Economic Community of West African States (ECOWAS), Southern African Development Community (SADC), East African Community (EAC), Economic Community of Central African States (ECCAS), Arab Maghreb Union (AMU), and others, can strengthen regional governance, fostering greater integration and stability, and promote regional peace, security, prosperity, and development in Africa.',
  },
]

export default function ProgrammesSection() {
  return (
    <section id="programmes" className="section-padding" aria-labelledby="programmes-heading">
      <div className="container">
        <h2 className="section-title center-title text-center mb-5" id="programmes-heading">Our Programmes</h2>
        <div className="row">
          <div className="col-lg-6">
            <FadeIn>
              {leftProgrammes.map((p) => (
                <article className="program-card" key={p.title} aria-label={p.title}>
                  <h3 className="h4">{p.title}</h3>
                  <p>{p.desc}</p>
                </article>
              ))}
            </FadeIn>
          </div>
          <div className="col-lg-6">
            <FadeIn>
              {rightProgrammes.map((p) => (
                <article className="program-card" key={p.title} aria-label={p.title}>
                  <h3 className="h4">{p.title}</h3>
                  <p>{p.desc}</p>
                </article>
              ))}
            </FadeIn>
          </div>
        </div>
      </div>
    </section>
  )
}
