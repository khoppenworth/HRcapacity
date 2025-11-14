import { FormEvent, useState } from 'react';
import { apiClient } from '../api/client';

interface LoginPageProps {
  onLogin: (token: string, tenant: string) => void;
}

const LoginPage = ({ onLogin }: LoginPageProps) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [tenant, setTenant] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    setError(null);
    setLoading(true);

    try {
      const response = await apiClient.post('/auth/login', { email, password }, {
        headers: {
          'X-Tenant': tenant
        }
      });

      onLogin(response.data.token, tenant);
    } catch (err: any) {
      setError(err.response?.data?.message ?? 'Login failed. Check your credentials.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="card" style={{ maxWidth: '420px', margin: '4rem auto' }}>
      <h2>HRassess Staff Portal</h2>
      <form onSubmit={handleSubmit}>
        <label htmlFor="tenant">Tenant Slug</label>
        <input id="tenant" value={tenant} onChange={(e) => setTenant(e.target.value)} required />

        <label htmlFor="email">Email</label>
        <input id="email" type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />

        <label htmlFor="password">Password</label>
        <input id="password" type="password" value={password} onChange={(e) => setPassword(e.target.value)} required />

        <button type="submit" disabled={loading}>
          {loading ? 'Signing in...' : 'Sign in'}
        </button>
        {error && <div className="error">{error}</div>}
      </form>
    </div>
  );
};

export default LoginPage;
