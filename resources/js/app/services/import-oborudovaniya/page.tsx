import type { Metadata } from 'next';
import Link from 'next/link';
import { ProductGallery } from '@/components/catalog/ProductGallery';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { emailHref, phoneHref, siteContacts } from '@/lib/site-content';
import {
    Button,
    Container,
    Group,
    Image,
    SimpleGrid,
    Stack,
    Text,
    TextInput,
    Textarea,
    Title,
} from '@mantine/core';
import {
    IconBuildingWarehouse,
    IconChecklist,
    IconMail,
    IconPhone,
    IconRouteSquare,
    IconShieldCheck,
    IconTruckDelivery,
} from '@tabler/icons-react';

export const metadata: Metadata = {
    title: 'Импорт оборудования | ЮНИК С',
    description: 'Ввоз, растаможка и поставка промышленного оборудования из стран ЕС и Азии с сопровождением под ключ.',
};

const galleryImages = Array.from({ length: 9 }, (_, index) => `/assets/img/services/import-page/gallery/${index + 1}.jpg`);

const offerItems = [
    'Поиск и подбор требуемого оборудования под ключ',
    'Поставка и растаможка оборудования за фиксированную стоимость',
    'Организация сложных релокаций и проектный менеджемент',
] as const;

const advantageCards = [
    {
        icon: IconChecklist,
        title: 'Компетенции и опыт',
        text: 'Наша компания "Юник С" появилась благодаря более чем 10-ти летнему опыту работы в сфере инжиниринга и управления проектами с российскими и иностранными промышленными компаниями. Мы принимали участие во многих проектах, в т.ч. связанных с перепроектированием сложных автоматизированных производственных линий, их перевозкой из-за границы и последующим запуском на территории РФ.',
    },
    {
        icon: IconRouteSquare,
        title: 'Четкое планирование',
        text: 'Мы точно знаем, сколько реального времени необходимо закладывать на подготовку и согласование контракта с иностранным поставщиком, какие вопросы нужно проговорить заранее, чтобы исключить изменение окончательной валютной стоимости, сколько времени потребуется на таможенное оформление, подготовку документов и логистику. И главное, мы знаем, как эти сроки реализовать на практике.',
    },
    {
        icon: IconShieldCheck,
        title: 'Взаимоотношения с таможней',
        text: 'Вопрос таможенной очистки самый ключевой при импорте и требует максимального внимания при ввозе оборудования из-за границы. При подготовке важно учитывать все аспекты таможенного права, чтобы не завысить таможенную стоимость, попасть под оптимальные пошлины и, главное, не застрять в длительном простое на этапе оформления. Поэтому мы работаем только с проверенным таможенным представителем.',
    },
    {
        icon: IconTruckDelivery,
        title: 'Надежные перевозчики',
        text: 'Выбор перевозчика для провоза оборудования через несколько границ также требует серьезного подхода. Нужно выбирать компании, специализирующиеся именно на международных перевозках, дающие гарантии, страховки и соблюдающие требуемые условия транспортировки, например прямую перевозку без перегрузки оборудования. У нас много партнеров среди международных логистических компаний под разные задачи, сроки и стоимость.',
    },
] as const;

function HeroSection() {
    return (
        <section className="page-hero buyout-hero import-hero">
            <Container size="xl">
                <div className="catalog-breadcrumbs">
                    <Link href="/">Главная</Link>
                    <span>/</span>
                    <Link href="/services">Услуги</Link>
                    <span>/</span>
                    <span>Импорт оборудования</span>
                </div>

                <Stack gap="lg" maw={860}>
                    <Title order={1}>Импорт оборудования</Title>
                    <Text size="lg">
                        С 2019 года мы сопровождаем поставки промышленного оборудования из Европы и Азии, берем на
                        себя переговоры с поставщиками, логистику, таможенное оформление и контроль сроков.
                    </Text>
                    <Text size="lg">
                        Если нужен надежный российский партнер по ввозу оборудования или отдельным этапам ВЭД, мы
                        подключаемся в удобном для вас формате.
                    </Text>
                    <Group gap="md">
                        <Button
                            component="a"
                            href={phoneHref(siteContacts.phone)}
                            size="lg"
                            leftSection={<IconPhone size={18} />}
                        >
                            Позвонить
                        </Button>
                        <Button
                            component="a"
                            href={`mailto:${siteContacts.email}?subject=%D0%98%D0%BC%D0%BF%D0%BE%D1%80%D1%82%20%D0%BE%D0%B1%D0%BE%D1%80%D1%83%D0%B4%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F`}
                            size="lg"
                            variant="white"
                            color="dark"
                            leftSection={<IconMail size={18} />}
                        >
                            Отправить запрос
                        </Button>
                    </Group>
                </Stack>
            </Container>
        </section>
    );
}

function IntroSection() {
    return (
        <section className="content-section content-section--white">
            <Container size="xl">
                <div className="import-intro-card">
                    <div className="import-intro-card__content">
                        <Stack gap="lg">
                            <Title order={2}>Ввоз и растаможка оборудования из стран ЕС и Азии</Title>
                            <Text>
                                Компания ООО «Юник С» начала работу в марте 2019 года, и одним из ключевых
                                направлений сразу стал импорт бывшего в эксплуатации оборудования из стран Европы. В
                                течение первого этапа мы активно занимались такими поставками, выстраивали связи с
                                иностранными поставщиками и на практике отрабатывали весь цикл ввоза техники в Россию.
                            </Text>
                            <Text>
                                Позже, на фоне ограничений и закрытых границ, активность в этом направлении снизилась,
                                и мы расширили географию поиска, подключив поставщиков с внутреннего рынка РФ. При
                                этом спрос на импортные поставки сохранился на стабильном уровне, поэтому мы
                                продолжаем сопровождать сделки по ввозу оборудования и сегодня.
                            </Text>
                        </Stack>
                    </div>

                    <div className="import-intro-card__media">
                        <Image
                            src="/assets/img/services/import-page/import-block.jpeg"
                            alt="Импорт и поставка промышленного оборудования"
                        />
                    </div>
                </div>
            </Container>
        </section>
    );
}

function OfferSection() {
    return (
        <section className="content-section import-offer-section">
            <Container size="xl">
                <div className="import-offer-stack">
                    <div className="buyout-regions-card">
                        <Stack gap="xl">
                            <Title order={2}>Мы предлагаем</Title>
                            <SimpleGrid cols={{ base: 1, md: 1 }} spacing="md">
                                {offerItems.map((item) => (
                                    <div key={item} className="buyout-list-item">
                                        <span className="buyout-list-item__dot" />
                                        <Text>{item}</Text>
                                    </div>
                                ))}
                            </SimpleGrid>

                            <blockquote className="sales-quote-card import-quote-card">
                                <p>
                                    Другими словами ООО “Юник С” может стать для вас как российским поставщиком
                                    импортного оборудования, так и партнером, оказывающим сопутствующие услуги. Для
                                    этого у нас есть все необходимые ресурсы и достаточный опыт.
                                </p>
                            </blockquote>
                        </Stack>
                    </div>

                    <section id="import-form" className="buyout-form-card import-form-card">
                        <Stack gap="md">
                            <Text className="contact-card__eyebrow">Заявка на импорт</Text>
                            <Title order={3}>Оставьте заявку</Title>
                            <Text c="dimmed">
                                Напишите, какое оборудование нужно привезти, и мы свяжемся с вами для уточнения
                                деталей по поставке.
                            </Text>
                        </Stack>

                        <Stack gap="sm" mt="xl">
                            <TextInput label="Имя" placeholder="Как к вам обращаться" />
                            <TextInput label="Email" placeholder="example@mail.ru" type="email" />
                            <TextInput label="Телефон" placeholder="+7 (___) ___-__-__" />
                            <Textarea
                                label="Сообщение"
                                placeholder="Опишите оборудование, страну поставки, сроки или дополнительные пожелания"
                                minRows={5}
                            />
                        </Stack>

                        <Button fullWidth size="lg" mt="xl">
                            Отправить заявку
                        </Button>
                        <Text size="sm" c="dimmed" className="buyout-form-card__hint">
                            Также можно написать на {siteContacts.email} или позвонить по номеру {siteContacts.phone}.
                        </Text>
                    </section>
                </div>
            </Container>
        </section>
    );
}

function GallerySection() {
    return (
        <section className="content-section content-section--white">
            <Container size="xl">
                <div className="instrument-layout">
                    <section className="instrument-card">
                        <Stack gap="xl">
                            <ProductGallery title="Импорт оборудования" images={galleryImages} />
                            <Text className="import-gallery-caption">
                                Фотографии самого первого токарного станка с ЧПУ NILES-SIMMONS N10, привезенного из
                                Германии в 2019 году для клиента в г. Тимашевск.
                            </Text>
                        </Stack>
                    </section>
                </div>
            </Container>
        </section>
    );
}

function AdvantagesSection() {
    return (
        <section className="content-section">
            <Container size="xl">
                <Stack gap="xl">
                    <div className="import-section-heading">
                        <Text className="contact-card__eyebrow">Преимущества</Text>
                        <Title order={2}>Для этого у нас есть всё необходимое:</Title>
                    </div>

                    <SimpleGrid cols={{ base: 1, md: 2 }} spacing="lg">
                        {advantageCards.map((item) => {
                            const Icon = item.icon;

                            return (
                                <article key={item.title} className="buyout-feature-card import-advantage-card">
                                    <span className="buyout-feature-card__icon">
                                        <Icon size={24} />
                                    </span>
                                    <Title order={3}>{item.title}</Title>
                                    <Text c="dimmed">{item.text}</Text>
                                </article>
                            );
                        })}
                    </SimpleGrid>

                    <div className="import-contact-band">
                        <Stack gap="md">
                            <Group gap="sm" align="center">
                                <span className="sales-search-card__icon">
                                    <IconBuildingWarehouse size={24} />
                                </span>
                                <Title order={3}>Подключимся к проекту в удобном формате</Title>
                            </Group>
                            <Text>
                                Можем взять на себя поставку под ключ или подключиться к отдельным этапам: подбору,
                                переговорам, таможне, логистике и документам.
                            </Text>
                            <Group gap="md">
                                <Button component="a" href={phoneHref(siteContacts.phone)} leftSection={<IconPhone size={18} />}>
                                    Связаться по телефону
                                </Button>
                                <Button
                                    component="a"
                                    href={emailHref(siteContacts.email)}
                                    variant="default"
                                    leftSection={<IconMail size={18} />}
                                >
                                    Написать на email
                                </Button>
                            </Group>
                        </Stack>
                    </div>
                </Stack>
            </Container>
        </section>
    );
}

export default function EquipmentImportPage() {
    return (
        <>
            <Header />
            <main>
                <HeroSection />
                <IntroSection />
                <OfferSection />
                <GallerySection />
                <AdvantagesSection />
            </main>
            <Footer />
        </>
    );
}
