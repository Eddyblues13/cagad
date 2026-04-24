import { useState } from 'react'

export default function JoinSection() {
  const [submitting, setSubmitting] = useState(false)
  const [success, setSuccess] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setSubmitting(true)

    const form = e.target
    const formData = new FormData(form)

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: formData,
      })

      if (response.ok) {
        form.reset()
        setSuccess(true)
      } else {
        alert('❌ Something went wrong. Please try again.')
      }
    } catch (error) {
      alert('⚠️ Error: ' + error.message)
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <section id="join" className="section-padding bg-light" aria-labelledby="join-heading">
      <div className="container">
        <div className="row">
          <div className="col-lg-10 mx-auto">
            <h2 className="section-title center-title text-center mb-5" id="join-heading">Join Us</h2>
            <p className="text-center lead mb-5">
              Become a part of our mission to advance governance and development in Africa. Volunteer your skills and
              expertise to make a difference.
            </p>

            <div className="card shadow-lg border-0">
              <div className="card-body p-5">
                <form
                  id="applicationForm"
                  action="https://formsubmit.co/info@cagad.org"
                  method="POST"
                  onSubmit={handleSubmit}
                  aria-label="Volunteer application form"
                  noValidate
                >
                  {/* Hidden fields */}
                  <input type="hidden" name="_captcha" value="false" />
                  <input type="hidden" name="_subject" value="New CAGAD Volunteer Application" />

                  <fieldset>
                    <legend className="visually-hidden">Personal Information</legend>
                    <div className="row">
                      <div className="col-md-6 mb-3">
                        <label htmlFor="firstName" className="form-label">First Name</label>
                        <input type="text" className="form-control" name="First Name" id="firstName" required autoComplete="given-name" />
                      </div>
                      <div className="col-md-6 mb-3">
                        <label htmlFor="lastName" className="form-label">Last Name</label>
                        <input type="text" className="form-control" name="Last Name" id="lastName" required autoComplete="family-name" />
                      </div>
                    </div>

                    <div className="row">
                      <div className="col-md-6 mb-3">
                        <label htmlFor="sex" className="form-label">Sex</label>
                        <select className="form-select" name="Sex" id="sex" required defaultValue="">
                          <option value="" disabled>Select</option>
                          <option value="Male">Male</option>
                          <option value="Female">Female</option>
                        </select>
                      </div>
                      <div className="col-md-6 mb-3">
                        <label htmlFor="ageRange" className="form-label">Age Range</label>
                        <select className="form-select" name="Age Range" id="ageRange" required defaultValue="">
                          <option value="" disabled>Select</option>
                          <option value="Below 20">Below 20</option>
                          <option value="20-25">20-25</option>
                          <option value="26-30">26-30</option>
                          <option value="31-35">31-35</option>
                          <option value="36-40">36-40</option>
                          <option value="41-45">41-45</option>
                          <option value="46+">46 and above</option>
                        </select>
                      </div>
                    </div>

                    <div className="row">
                      <div className="col-md-6 mb-3">
                        <label htmlFor="email" className="form-label">Email Address</label>
                        <input type="email" className="form-control" name="Email" id="email" required autoComplete="email" />
                      </div>
                      <div className="col-md-6 mb-3">
                        <label htmlFor="phone" className="form-label">Phone Number</label>
                        <input type="tel" className="form-control" name="Phone" id="phone" placeholder="+2349123733985" required autoComplete="tel" />
                      </div>
                    </div>

                    <div className="row">
                      <div className="col-md-6 mb-3">
                        <label htmlFor="countryOrigin" className="form-label">Country of Origin</label>
                        <input type="text" className="form-control" name="Country of Origin" id="countryOrigin" required autoComplete="country-name" />
                      </div>
                      <div className="col-md-6 mb-3">
                        <label htmlFor="countryResidence" className="form-label">Country of Residence</label>
                        <input type="text" className="form-control" name="Country of Residence" id="countryResidence" required autoComplete="country-name" />
                      </div>
                    </div>

                    <div className="mb-3">
                      <label htmlFor="cityResidence" className="form-label">City of Residence</label>
                      <input type="text" className="form-control" name="City of Residence" id="cityResidence" required autoComplete="address-level2" />
                    </div>
                  </fieldset>

                  <fieldset>
                    <legend className="visually-hidden">Volunteer Details</legend>
                    <div className="mb-3">
                      <label htmlFor="hoursCommit" className="form-label">How many hours can you commit per week?</label>
                      <select className="form-select" name="Hours Commitment" id="hoursCommit" required defaultValue="">
                        <option value="" disabled>Select</option>
                        <option value="2-5 hours">2-5 hours</option>
                        <option value="5-10 hours">5-10 hours</option>
                        <option value="10-15 hours">10-15 hours</option>
                        <option value="15-20 hours">15-20 hours</option>
                        <option value="20+ hours">20+ hours</option>
                      </select>
                    </div>

                    <div className="mb-3">
                      <label htmlFor="skills" className="form-label">Skills and Expertise</label>
                      <textarea className="form-control" name="Skills" id="skills" rows="3" placeholder="Please describe your skills and areas of expertise"></textarea>
                    </div>

                    <div className="mb-4">
                      <label htmlFor="interest" className="form-label">Areas of Interest</label>
                      <select className="form-select" name="Areas of Interest" id="interest" required defaultValue="">
                        <option value="" disabled>Select an area</option>
                        <option value="Research">Research</option>
                        <option value="Advocacy">Advocacy</option>
                        <option value="Community Engagement">Community Engagement</option>
                        <option value="Technology & Innovation">Technology &amp; Innovation</option>
                        <option value="Administration">Administration</option>
                        <option value="Communications">Communications</option>
                        <option value="Fundraising">Fundraising</option>
                      </select>
                    </div>
                  </fieldset>

                  <div className="text-center mt-4">
                    <button type="submit" id="submitBtn" className="btn btn-primary btn-lg px-5" disabled={submitting} aria-busy={submitting}>
                      {submitting ? 'Submitting... ⏳' : 'Submit Application'}
                    </button>
                  </div>
                </form>

                {/* Success Message */}
                {success && (
                  <div className="alert alert-success mt-4 text-center" role="alert" aria-live="polite">
                    ✅ Thank you! Your application has been submitted successfully.
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
