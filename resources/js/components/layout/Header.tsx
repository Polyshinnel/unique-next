'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import {
    Anchor,
    Box,
    Burger,
    Button,
    Container,
    Divider,
    Drawer,
    Image,
    Stack,
} from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import { IconMail, IconPhone } from '@tabler/icons-react';
import { emailHref, navItems, phoneHref, siteContacts } from '@/lib/site-content';

const headerInactiveRoutes = new Set(['/ohrana-truda', '/private-policy']);

function isNavItemActive(currentPath: string, itemHref: string) {
    if (headerInactiveRoutes.has(currentPath)) {
        return false;
    }

    if (itemHref === '/') {
        return currentPath === '/';
    }

    return currentPath === itemHref || currentPath.startsWith(`${itemHref}/`);
}

export function Header() {
    const [opened, { toggle, close }] = useDisclosure(false);
    const pathname = usePathname() ?? '/';

    const menu = (
        <nav className="site-nav">
            {navItems.map((item) => (
                <Link
                    key={item.href}
                    href={item.href}
                    className={`site-nav__link${isNavItemActive(pathname, item.href) ? ' site-nav__link--active' : ''}`}
                    aria-current={isNavItemActive(pathname, item.href) ? 'page' : undefined}
                    onClick={close}
                >
                    {item.label}
                </Link>
            ))}
        </nav>
    );

    return (
        <header className="site-header">
            <Container size="xl" className="site-header__inner">
                <Link href="/" className="site-logo" aria-label="ЮНИК С">
                    <Image src="/assets/img/unique-logo.png" alt="ЮНИК С" />
                </Link>

                <Box className="site-header__nav">{menu}</Box>

                <div className="site-header__contacts">
                    <Anchor href={phoneHref(siteContacts.phone)} className="contact-link">
                        <IconPhone size={18} className="contact-link__icon" />
                        <span className="contact-link__text">{siteContacts.phone}</span>
                    </Anchor>
                    <Anchor href={emailHref(siteContacts.email)} className="contact-link">
                        <IconMail size={18} className="contact-link__icon" />
                        <span className="contact-link__text">{siteContacts.email}</span>
                    </Anchor>
                </div>

                <Burger opened={opened} onClick={toggle} hiddenFrom="md" aria-label="Меню" />
            </Container>

            <Drawer opened={opened} onClose={close} position="right" title="ЮНИК С" size="sm">
                <Stack gap="lg">
                    {menu}
                    <Divider />
                    <Anchor href={phoneHref(siteContacts.phone)} className="contact-link">
                        <IconPhone size={18} />
                        <span>{siteContacts.phone}</span>
                    </Anchor>
                    <Anchor href={emailHref(siteContacts.email)} className="contact-link">
                        <IconMail size={18} />
                        <span>{siteContacts.email}</span>
                    </Anchor>
                    <Button component="a" href="/contacts" onClick={close}>
                        Связаться
                    </Button>
                </Stack>
            </Drawer>
        </header>
    );
}
