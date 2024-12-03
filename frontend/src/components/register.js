import React, { useState } from "react"

const RegistrationForm = () => {
  const [formData, setFormData] = useState({
    email: "",
    first_name: "",
    last_name: "",
    postal_code: "",
    how_did_you_hear: "",
    is_realtor: false,
  })
  const [message, setMessage] = useState("")

  const handleChange = e => {
    const { name, value, type, checked } = e.target
    setFormData({ ...formData, [name]: type === "checkbox" ? checked : value })
  }

  const handleSubmit = async e => {
    e.preventDefault()
    const response = await fetch("/backend/register.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(formData),
    })
    const result = await response.text()
    setMessage(result)
  }

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="text"
        name="first_name"
        placeholder="First Name"
        onChange={handleChange}
        required
      />
      <input
        type="text"
        name="last_name"
        placeholder="Last Name"
        onChange={handleChange}
        required
      />
      <input
        type="text"
        name="postal_code"
        placeholder="Postal Code"
        onChange={handleChange}
        required
      />
      <select name="how_did_you_hear" onChange={handleChange} required>
        <option value="">How did you hear about us?</option>
        <option value="Google">Google</option>
        <option value="Friend">Friend</option>
      </select>
      <label>
        <input type="checkbox" name="is_realtor" onChange={handleChange} />
        Are you a realtor?
      </label>
      <div className="g-recaptcha" data-sitekey="your-recaptcha-site-key"></div>
      <button type="submit">Register</button>
      <p>{message}</p>
    </form>
  )
}

export default RegistrationForm
