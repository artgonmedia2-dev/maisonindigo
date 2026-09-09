export type ErrorStatus = 403 | 404 | 500 | 503;

export interface ErrorPageProps {
    status: ErrorStatus;
}
