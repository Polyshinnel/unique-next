export type FeedbackForm = 'Обратная связь' | 'Заявка на импорт' | 'Заявка на выкуп' | 'Обратный звонок';
import { getClientApiUrl } from '@/lib/api';

export async function submitFeedback(form: FeedbackForm, fields: Record<string, string>): Promise<void> {
    let response: Response;
    try {
        response = await fetch(`${getClientApiUrl()}/feedback`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ form, fields }),
        });
    } catch {
        throw new Error('Не удалось отправить заявку. Проверьте соединение и попробуйте ещё раз.');
    }

    if (!response.ok) {
        const payload = await response.json().catch(() => null) as { message?: string } | null;
        throw new Error(payload?.message ?? 'Не удалось отправить заявку. Попробуйте ещё раз.');
    }
}
