// Mirror dari app/Enums/ di Laravel — satu sumber kebenaran. Update keduanya jika berubah.
// Jangan hardcode string status di template; selalu import dari sini.

export const MemberStatus = Object.freeze({
  PENDING_VERIFICATION: 'pending_verification',
  WAITING_SURVEY: 'waiting_survey',
  ACTIVE: 'active',
  REJECTED: 'rejected',
  EXPIRED: 'expired',
})

export const SurveyLevel = Object.freeze({
  DPC: 'dpc',
  DPD: 'dpd',
  DPP: 'dpp',
})

export const SurveyStatus = Object.freeze({
  PENDING: 'pending',
  ACCEPTED: 'accepted',
  APPROVED: 'approved',
  REJECTED: 'rejected',
  ESCALATED: 'escalated',
})

export const PaymentType = Object.freeze({
  REGISTRATION: 'registration',
  RENEWAL: 'renewal',
})

export const PaymentStatus = Object.freeze({
  PENDING: 'pending',
  PAID: 'paid',
  FAILED: 'failed',
  EXPIRED: 'expired',
  REFUNDED: 'refunded',
})

export const RefundStatus = Object.freeze({
  QUEUED: 'queued',
  PROCESSING: 'processing',
  COMPLETED: 'completed',
  CANCELLED: 'cancelled',
})

export const SanctionSeverity = Object.freeze({
  WARNING: 'warning',
  SUSPENSION: 'suspension',
  TERMINATION: 'termination',
})

export const StarterkitDistributionStatus = Object.freeze({
  PENDING: 'pending',
  DISTRIBUTED: 'distributed',
  CONFIRMED: 'confirmed',
})

export const UserRole = Object.freeze({
  SUPER_ADMIN: 'super_admin',
  DPP: 'dpp',
  DPD: 'dpd',
  DPC: 'dpc',
  MEMBER: 'member',
})
