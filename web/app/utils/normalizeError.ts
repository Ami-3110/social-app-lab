// ~/utils/normalizeError.ts
import type { NormalizedApiErrors, FieldErrors } from '~/types/api';

type MaybeFetchError = {
    statusCode?: number;
    statusMessage?: string;
    message?: string;
    data?: any;
    response?: { status?: number; statusText?: string; _data?: any };
    error?: { data?: any; message?: string; statusCode?: number };
};

function pickPayload(e: MaybeFetchError) {
    // ofetch のどこに入っていても拾う
    return (
        e?.data ??
        e?.error?.data ??
        e?.response?._data ??
        (typeof (e as any)?.payload === 'object' ? (e as any).payload : null) ??
        null
    );
}

export function normalizeApiError(err: unknown): NormalizedApiErrors {
    const e = err as MaybeFetchError;

    const status =
        e?.statusCode ??
        e?.error?.statusCode ??
        e?.response?.status ??
        (typeof (e as any)?.status === 'number' ? (e as any).status : 0);

    const payload = pickPayload(e);

    // 422: Laravel ほかの典型
    if (status === 422 && payload && typeof payload === 'object') {
        // どこにあっても拾う
        const candidateErrors =
            (payload as any).errors ??
            (payload as any)?.error?.errors ??
            (payload as any)?.data?.errors ??
            undefined;

        const fieldErrors: FieldErrors | undefined =
            candidateErrors && typeof candidateErrors === 'object'
                ? candidateErrors
                : undefined;

        const message =
            typeof (payload as any).message === 'string'
                ? (payload as any).message
                : 'Validation failed.';

        return {
            status: 422,
            message,
            fieldErrors,
            raw: payload,
        };
    }

    // 認証・権限系
    if (status === 401) {
        const message = payload?.message || e?.statusMessage || e?.message || 'Unauthorized.';
        return { status: 401, message, raw: payload ?? e };
    }
    if (status === 403) {
        const message = payload?.message || 'Forbidden.';
        return { status: 403, message, raw: payload ?? e };
    }
    if (status === 404) {
        const message = payload?.message || 'Not found.';
        return { status: 404, message, raw: payload ?? e };
    }

    // 汎用
    const genericMessage =
        payload?.message ||
        e?.statusMessage ||
        e?.message ||
        (status >= 500 && 'Server error.') ||
        'Request failed.';

    return {
        status: status || 0,
        message: genericMessage,
        raw: payload ?? e,
    };
}
