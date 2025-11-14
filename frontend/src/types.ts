export interface QuestionnaireItem {
  id: number;
  type: 'likert' | 'boolean' | 'text';
  code: string;
  text: string;
  help_text?: string | null;
  is_required: boolean;
  order_index: number;
  weight_percent?: number | null;
}

export interface QuestionnaireSection {
  id: number;
  title: string;
  order_index: number;
  items: QuestionnaireItem[];
}

export interface QuestionnaireVersion {
  id: number;
  version_number: number;
  status: string;
  sections: QuestionnaireSection[];
}

export interface WorkFunction {
  id: number;
  name: string;
  code: string;
}

export interface AssessmentResponseDTO {
  questionnaire_item_id: number;
  raw_value?: string | null;
  numeric_value?: number | null;
}

export interface AssessmentDTO {
  id: number;
  status: 'draft' | 'submitted';
  score_percent?: number | null;
  performance_period: string;
  questionnaire_version: QuestionnaireVersion;
  responses: AssessmentResponseDTO[];
  work_function: WorkFunction;
}
