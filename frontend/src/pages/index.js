import * as React from "react"
import Layout from "../components/layout"
import Seo from "../components/seo"
import RegistrationForm from "../components/register"

const IndexPage = () => (
  <Layout>
    <RegistrationForm />
  </Layout>
)
export const Head = () => <Seo title="Home" />

export default IndexPage
