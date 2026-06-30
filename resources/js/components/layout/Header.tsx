import { HeaderClient } from '@/components/layout/HeaderClient';
import { getSiteContacts } from '@/lib/site-contacts';

export async function Header() {
    const contacts = await getSiteContacts();

    return <HeaderClient phone={contacts.phone} email={contacts.email} />;
}
