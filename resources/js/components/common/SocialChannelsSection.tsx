import Image from 'next/image';
import { Badge, Button, Container, SimpleGrid, Stack, Text, Title } from '@mantine/core';

const channels = [
    {
        name: 'Telegram',
        badgeColor: 'cyan',
        href: 'https://t.me/uniqset_catalog',
        qrSrc: '/assets/img/qr-telegram-uniqset-catalog.svg',
        qrAlt: 'QR-код канала ЮНИК С в Telegram',
        buttonLabel: 'Канал в telegram',
    },
    {
        name: 'MAX',
        badgeColor: 'dark',
        href: 'https://max.ru/id4027139409_biz',
        qrSrc: '/assets/img/qr-max-uniqset.svg',
        qrAlt: 'QR-код канала ЮНИК С в MAX',
        buttonLabel: 'Канал в МАКСе',
    },
] as const;

type SocialChannelsSectionProps = {
    productPage?: boolean;
};

export function SocialChannelsSection({ productPage = false }: SocialChannelsSectionProps) {
    return (
        <section className={`social-channels-section${productPage ? ' social-channels-section--product' : ''}`}>
            <Container size="xl">
                <div className="social-channels-block">
                    <div className="social-channels-block__copy">
                        <div className="social-channels-block__badges">
                            {channels.map((channel) => (
                                <Badge key={channel.name} variant="light" color={channel.badgeColor} size="lg">
                                    {channel.name}
                                </Badge>
                            ))}
                        </div>
                        <Title order={2}>Наши группы в Telegram и MAX</Title>
                        <Text className="social-channels-block__description">
                            Все новинки, изменения цены и условий продажи мгновенно публикуем в наших каналах в Максе и
                            в Телеграм
                        </Text>
                        <Text className="social-channels-block__accent">
                            Подписывайтесь, чтобы ничего не пропустить!
                        </Text>
                    </div>

                    <div className="social-channels-block__channels">
                        <SimpleGrid cols={{ base: 1, sm: 2 }} spacing="md">
                            {channels.map((channel) => (
                                <Stack key={channel.name} className="social-channel-card" gap="md">
                                    <div className="social-channel-card__qr">
                                        <Image src={channel.qrSrc} alt={channel.qrAlt} width={220} height={220} unoptimized />
                                    </div>
                                    <Button
                                        component="a"
                                        href={channel.href}
                                        target="_blank"
                                        rel="noreferrer"
                                        fullWidth
                                    >
                                        {channel.buttonLabel}
                                    </Button>
                                </Stack>
                            ))}
                        </SimpleGrid>
                        <Text className="social-channels-block__note" c="dimmed" size="sm">
                            Сканируйте QR-код, чтобы открыть страницу сразу на телефоне.
                        </Text>
                    </div>
                </div>
            </Container>
        </section>
    );
}
