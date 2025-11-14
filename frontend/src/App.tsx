import { useEffect, useState } from 'react';
import { Navigate, Route, Routes, useLocation, useNavigate } from 'react-router-dom';
import { getAuth, setAuth } from './api/client';
import AssessmentFormPage from './pages/AssessmentFormPage';
import AssessmentListPage from './pages/AssessmentListPage';
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
  const [auth, setAuthState] = useState(getAuth());

  useEffect(() => {
    setAuthState(getAuth());
  }, []);

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

  return (
    <div className="app-container">
      {auth.token && (
        <div style={{ textAlign: 'right', marginBottom: '1rem' }}>
          <button className="secondary" onClick={handleLogout}>
            Logout
          </button>
        </div>
      )}
      <Routes>
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
        <Route path="*" element={<Navigate to="/assessments" />} />
      </Routes>
    </div>
  );
}

export default App;
