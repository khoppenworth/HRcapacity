import { FormEvent, useEffect, useMemo, useState } from 'react';
import { useParams } from 'react-router-dom';
import { apiClient } from '../api/client';
import { AssessmentDTO, AssessmentResponseDTO, QuestionnaireItem } from '../types';

type ResponseState = Record<number, AssessmentResponseDTO>;

const AssessmentFormPage = () => {
  const { id } = useParams();
  const [assessment, setAssessment] = useState<AssessmentDTO | null>(null);
  const [responses, setResponses] = useState<ResponseState>({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);

  useEffect(() => {
    const fetchAssessment = async () => {
      try {
        const response = await apiClient.get<AssessmentDTO>(`/my/assessments/${id}`);
        setAssessment(response.data);
        const initialResponses: ResponseState = {};
        response.data.responses?.forEach((resp) => {
          initialResponses[resp.questionnaire_item_id] = resp;
        });
        setResponses(initialResponses);
      } catch (err: any) {
        setError(err.response?.data?.message ?? 'Failed to load assessment.');
      } finally {
        setLoading(false);
      }
    };

    if (id) {
      fetchAssessment();
    }
  }, [id]);

  const handleLikertChange = (itemId: number, value: number) => {
    setResponses((prev) => ({
      ...prev,
      [itemId]: { questionnaire_item_id: itemId, numeric_value: value }
    }));
  };

  const handleBooleanChange = (itemId: number, value: boolean) => {
    setResponses((prev) => ({
      ...prev,
      [itemId]: { questionnaire_item_id: itemId, raw_value: value ? '1' : '0' }
    }));
  };

  const handleTextChange = (itemId: number, value: string) => {
    setResponses((prev) => ({
      ...prev,
      [itemId]: { questionnaire_item_id: itemId, raw_value: value }
    }));
  };

  const payloadResponses = useMemo(() => Object.values(responses), [responses]);

  const sendRequest = async (endpoint: string) => {
    if (!assessment) return;
    setSaving(true);
    setError(null);
    setSuccess(null);

    try {
      const response = await apiClient.put<AssessmentDTO>(`/my/assessments/${assessment.id}/${endpoint}`, {
        responses: payloadResponses
      });
      setAssessment(response.data);
      setSuccess(endpoint === 'submit' ? 'Assessment submitted successfully.' : 'Draft saved.');
    } catch (err: any) {
      setError(err.response?.data?.message ?? 'Failed to save assessment.');
    } finally {
      setSaving(false);
    }
  };

  const handleSubmit = (event: FormEvent) => {
    event.preventDefault();
    sendRequest('submit');
  };

  if (loading) {
    return <div className="card">Loading assessment...</div>;
  }

  if (error && !assessment) {
    return <div className="card error">{error}</div>;
  }

  if (!assessment) {
    return null;
  }

  const isSubmitted = assessment.status === 'submitted';

  const renderItem = (item: QuestionnaireItem) => {
    const response = responses[item.id];

    if (item.type === 'likert') {
      const selected = response?.numeric_value;
      return (
        <div>
          <div style={{ display: 'flex', gap: '0.75rem' }}>
            {[1, 2, 3, 4, 5].map((value) => (
              <label key={value} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                <input
                  type="radio"
                  name={`likert-${item.id}`}
                  value={value}
                  checked={selected === value}
                  onChange={() => handleLikertChange(item.id, value)}
                  disabled={isSubmitted}
                />
                <span>{value}</span>
              </label>
            ))}
          </div>
        </div>
      );
    }

    if (item.type === 'boolean') {
      const value = response?.raw_value === '1';
      return (
        <select
          value={response?.raw_value ?? ''}
          onChange={(e) => handleBooleanChange(item.id, e.target.value === '1')}
          disabled={isSubmitted}
        >
          <option value="">Select</option>
          <option value="1">Yes</option>
          <option value="0">No</option>
        </select>
      );
    }

    return (
      <textarea
        value={response?.raw_value ?? ''}
        onChange={(e) => handleTextChange(item.id, e.target.value)}
        disabled={isSubmitted}
        rows={4}
      />
    );
  };

  return (
    <form className="card" onSubmit={handleSubmit}>
      <h2>{assessment.work_function.name}</h2>
      <p>Performance period: {assessment.performance_period}</p>
      <p>Status: {assessment.status}</p>
      {assessment.score_percent != null && <p>Score: {assessment.score_percent}%</p>}

      {assessment.questionnaire_version.sections.map((section) => (
        <div key={section.id} style={{ marginTop: '1.5rem' }}>
          <h3>{section.title}</h3>
          {section.items.map((item) => (
            <div key={item.id} style={{ marginBottom: '1rem' }}>
              <strong>{item.text}</strong>
              {item.help_text && <p>{item.help_text}</p>}
              {renderItem(item)}
            </div>
          ))}
        </div>
      ))}

      {error && <div className="error">{error}</div>}
      {success && <div style={{ color: '#047857', marginTop: '0.5rem' }}>{success}</div>}

      {!isSubmitted && (
        <div className="button-row">
          <button type="button" className="secondary" disabled={saving} onClick={() => sendRequest('save-draft')}>
            {saving ? 'Saving...' : 'Save draft'}
          </button>
          <button type="submit" disabled={saving}>
            {saving ? 'Submitting...' : 'Submit'}
          </button>
        </div>
      )}
    </form>
  );
};

export default AssessmentFormPage;
