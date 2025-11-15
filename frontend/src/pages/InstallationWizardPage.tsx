import { FormEvent, useEffect, useMemo, useState } from 'react';
import {
  InstallationPayload,
  InstallationStatus,
  SystemCheck,
  completeInstallation
} from '../api/install';

interface InstallationWizardPageProps {
  status: InstallationStatus;
  onRefresh: () => Promise<void>;
}

const defaultForm: InstallationPayload = {
  company_name: '',
  company_slug: '',
  contact_email: '',
  admin_name: '',
  admin_email: '',
  admin_password: '',
  admin_password_confirmation: ''
};

function InstallationWizardPage({ status, onRefresh }: InstallationWizardPageProps) {
  const [step, setStep] = useState(1);
  const [form, setForm] = useState<InstallationPayload>(defaultForm);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);

  const allChecksPassed = useMemo(
    () => status.checks.every((check: SystemCheck) => check.passed),
    [status.checks]
  );

  useEffect(() => {
    if (step === 2 && !allChecksPassed) {
      setStep(1);
    }
  }, [allChecksPassed, step]);

  const handleNext = () => {
    if (allChecksPassed) {
      setStep(2);
    }
  };

  const handleChange = (field: keyof InstallationPayload, value: string) => {
    setForm((prev) => ({
      ...prev,
      [field]: value
    }));
  };

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    setSubmitting(true);
    setError(null);
    setSuccessMessage(null);

    try {
      await completeInstallation(form);
      setSuccessMessage('Installation completed. You can now sign in using the new administrator account.');
      await onRefresh();
    } catch (err: any) {
      const validationErrors = (err.response?.data?.errors ?? {}) as Record<string, string[]>;
      const validationMessages = Object.values(validationErrors).flat();
      const message = validationMessages.join(' ') || err.response?.data?.message || 'Installation failed. Check the form and try again.';
      setError(message);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="card">
      <h2>HRcapacity Setup Wizard</h2>
      <p>Follow the guided steps to verify your server configuration and provision the first tenant administrator.</p>

      <div className="wizard-steps">
        <div className={`wizard-step ${step === 1 ? 'active' : ''}`}>1. Environment checks</div>
        <div className={`wizard-step ${step === 2 ? 'active' : ''}`}>2. Create administrator</div>
      </div>

      {step === 1 && (
        <div>
          <h3>System requirements</h3>
          <p>Review the checklist below. All items must be green before continuing.</p>
          <ul className="checklist">
            {status.checks.map((check) => (
              <li key={check.name} className={check.passed ? 'pass' : 'fail'}>
                <span className="indicator" aria-hidden>{check.passed ? '✔' : '✖'}</span>
                <div>
                  <strong>{check.name}</strong>
                  <div className="details">{check.details}</div>
                </div>
              </li>
            ))}
          </ul>
          <div className="button-row">
            <button type="button" onClick={() => void onRefresh()} disabled={submitting}>
              Re-run checks
            </button>
            <button type="button" onClick={handleNext} disabled={!allChecksPassed}>
              Continue
            </button>
          </div>
        </div>
      )}

      {step === 2 && (
        <form onSubmit={handleSubmit} className="wizard-form">
          <h3>Tenant details</h3>
          <label htmlFor="company_name">Company name</label>
          <input
            id="company_name"
            value={form.company_name}
            onChange={(event) => handleChange('company_name', event.target.value)}
            required
            maxLength={255}
          />

          <label htmlFor="company_slug">Tenant slug</label>
          <input
            id="company_slug"
            value={form.company_slug}
            onChange={(event) => handleChange('company_slug', event.target.value)}
            required
            maxLength={50}
            placeholder="e.g. acme"
          />

          <label htmlFor="contact_email">Primary contact email (optional)</label>
          <input
            id="contact_email"
            type="email"
            value={form.contact_email}
            onChange={(event) => handleChange('contact_email', event.target.value)}
          />

          <h3>Administrator</h3>
          <label htmlFor="admin_name">Full name</label>
          <input
            id="admin_name"
            value={form.admin_name}
            onChange={(event) => handleChange('admin_name', event.target.value)}
            required
            maxLength={255}
          />

          <label htmlFor="admin_email">Email</label>
          <input
            id="admin_email"
            type="email"
            value={form.admin_email}
            onChange={(event) => handleChange('admin_email', event.target.value)}
            required
            maxLength={255}
          />

          <label htmlFor="admin_password">Password</label>
          <input
            id="admin_password"
            type="password"
            value={form.admin_password}
            onChange={(event) => handleChange('admin_password', event.target.value)}
            required
            minLength={8}
          />

          <label htmlFor="admin_password_confirmation">Confirm password</label>
          <input
            id="admin_password_confirmation"
            type="password"
            value={form.admin_password_confirmation}
            onChange={(event) => handleChange('admin_password_confirmation', event.target.value)}
            required
            minLength={8}
          />

          {error && <div className="error" role="alert">{error}</div>}
          {successMessage && <div className="success" role="status">{successMessage}</div>}

          <div className="button-row">
            <button type="button" onClick={() => setStep(1)} className="secondary" disabled={submitting}>
              Back
            </button>
            <button type="submit" disabled={submitting}>
              {submitting ? 'Installing...' : 'Finish setup'}
            </button>
          </div>
        </form>
      )}
    </div>
  );
}

export default InstallationWizardPage;

