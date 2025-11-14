import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { apiClient } from '../api/client';
import { AssessmentDTO } from '../types';

const AssessmentListPage = () => {
  const [assessments, setAssessments] = useState<AssessmentDTO[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchAssessments = async () => {
      try {
        const response = await apiClient.get<AssessmentDTO[]>('/my/assessments');
        setAssessments(response.data);
      } catch (err: any) {
        setError(err.response?.data?.message ?? 'Failed to load assessments.');
      } finally {
        setLoading(false);
      }
    };

    fetchAssessments();
  }, []);

  if (loading) {
    return <div className="card">Loading assessments...</div>;
  }

  if (error) {
    return <div className="card error">{error}</div>;
  }

  if (!assessments.length) {
    return <div className="card">No assessments assigned yet.</div>;
  }

  return (
    <div>
      <h2>Your Assessments</h2>
      {assessments.map((assessment) => (
        <div className="card" key={assessment.id}>
          <h3>{assessment.work_function.name}</h3>
          <p>Period: {assessment.performance_period}</p>
          <p>Status: {assessment.status}</p>
          {assessment.score_percent != null && <p>Score: {assessment.score_percent}%</p>}
          <Link to={`/assessments/${assessment.id}`}>Open assessment</Link>
        </div>
      ))}
    </div>
  );
};

export default AssessmentListPage;
