import { useCallback, useEffect, useState } from 'react';
import { Navigate, Route, Routes, useLocation, useNavigate } from 'react-router-dom';
import { getAuth, setAuth } from './api/client';
import { InstallationStatus, fetchInstallationStatus } from './api/install';
import AssessmentFormPage from './pages/AssessmentFormPage';
import AssessmentListPage from './pages/AssessmentListPage';
import InstallationWizardPage from './pages/InstallationWizardPage';
import LoginPage from './pages/LoginPage';

function RequireAuth({ children }: { children: JSX.Element }) {
  const location = useLocation();
  const auth = getAuth();

  if (!auth.token) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  return children;
}

function App() {
  const navigate = useNavigate();
  const location = useLocation();
  const [auth, setAuthState] = useState(getAuth());
  const [installationStatus, setInstallationStatus] = useState<InstallationStatus | null>(null);
  const [loadingInstallStatus, setLoadingInstallStatus] = useState(true);
  const [installError, setInstallError] = useState<string | null>(null);

  const refreshInstallationStatus = useCallback(
    async (options?: { showLoading?: boolean }) => {
      const shouldShowLoading = options?.showLoading ?? installationStatus === null;

      if (shouldShowLoading) {
        setLoadingInstallStatus(true);
      }

      try {
        const status = await fetchInstallationStatus();
        setInstallationStatus(status);
        setInstallError(null);
      } catch (error: any) {
        const message =
          error?.response?.data?.message ??
          'Unable to load installation status. Confirm the API service is reachable.';
        setInstallError(message);
        if (!installationStatus) {
          setInstallationStatus(null);
        }
      } finally {
        if (shouldShowLoading) {
          setLoadingInstallStatus(false);
        }
      }
    },
    [installationStatus]
  );

  useEffect(() => {
    setAuthState(getAuth());
  }, []);

  useEffect(() => {
    refreshInstallationStatus({ showLoading: true });
  }, [refreshInstallationStatus]);

  useEffect(() => {
    if (loadingInstallStatus || !installationStatus) {
      return;
    }

    if (!installationStatus.isConfigured && location.pathname !== '/install') {
      navigate('/install', { replace: true });
    }

    if (installationStatus.isConfigured && location.pathname === '/install') {
      navigate('/login', { replace: true });
    }
  }, [installationStatus, loadingInstallStatus, location.pathname, navigate]);

  const handleLogin = (token: string, tenant: string) => {
    setAuth(token, tenant);
    setAuthState(getAuth());
    navigate('/assessments');
  };

  const handleLogout = () => {
    setAuth(null, null);
    setAuthState(getAuth());
    navigate('/login');
  };

  if (loadingInstallStatus) {
    return (
      <div className="app-container">
        <div className="card">
          <p>Loading configuration...</p>
        </div>
      </div>
    );
  }

  if (!installationStatus) {
    const message = installError ?? 'Unable to load installation status. Confirm the API service is reachable.';
    return (
      <div className="app-container">
        <div className="card">
          <p className="error" role="alert">{message}</p>
          <div className="button-row">
            <button onClick={() => refreshInstallationStatus({ showLoading: true })}>Retry</button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="app-container">
      {auth.token && installationStatus.isConfigured && (
        <div style={{ textAlign: 'right', marginBottom: '1rem' }}>
          <button className="secondary" onClick={handleLogout}>
            Logout
          </button>
        </div>
      )}
      <Routes>
        <Route
          path="/install"
          element={
            !installationStatus.isConfigured ? (
              <InstallationWizardPage
                status={installationStatus}
                onRefresh={() => refreshInstallationStatus({ showLoading: false })}
              />
            ) : (
              <Navigate to="/login" />
            )
          }
        />
        <Route path="/login" element={<LoginPage onLogin={handleLogin} />} />
        <Route
          path="/assessments"
          element={
            <RequireAuth>
              <AssessmentListPage />
            </RequireAuth>
          }
        />
        <Route
          path="/assessments/:id"
          element={
            <RequireAuth>
              <AssessmentFormPage />
            </RequireAuth>
          }
        />
        <Route
          path="*"
          element={<Navigate to={installationStatus.isConfigured ? '/assessments' : '/install'} />} />
      </Routes>
    </div>
  );
}

export default App;
